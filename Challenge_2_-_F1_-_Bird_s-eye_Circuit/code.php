<?php


class Tokenizer
{
    const TKN_START = 1;
    const TKN_STR_UP = 11;
    const TKN_STR_DOWN = 12;
    const TKN_STR_RIGTH = 21;
    const TKN_STR_LEFT = 22;
    const TKN_CUR_RIGTH = 31;
    const TKN_CUR_LEFT = 32;

    const SYM_START = '#';
    const SYM_CUR_LEFT = '/';
    const SYM_CUR_RIGTH = '\\';
    const SYM_STRAIGHT = '-';

    const UP = 1;
    const RIGTH = 2;
    const DOWN = 3;
    const LEFT = 4;

    public function tokenize($pattern)
    {
        if (!$pattern) {
            $pattern = $this->readFromSTDIN();
        }

        $this->setStartAtBeginingOfTheTrack($pattern);
        
        return $this->extractTokens($pattern);
    }


    private function setStartAtBeginingOfTheTrack(&$pattern)
    {
        $start = strpos($pattern, self::SYM_START);

        $pattern = 
            substr($pattern, $start, strlen($pattern)) . 
            substr($pattern, 0, $start);
    }

    private function extractTokens($pattern)
    {
        $direction = self::RIGTH;

        $output = [];
        $length = strlen($pattern);
        for($i=0;$i<$length;$i++) {
            var_dump($pattern[$i]);
            switch ($pattern[$i]) {
                case self::SYM_START:
                    $output[] = self::TKN_START;
                    break;
                case self::SYM_STRAIGHT:
                    $output[] = $this->getStraighToken($direction);
                    break;
                case self::SYM_CUR_RIGTH:
                    $direction = $this->calculateNewDirection($direction, $pattern[$i]);
                    $output[] = self::TKN_CUR_RIGTH;
                    break;
                case self::SYM_CUR_LEFT:
                    $direction = $this->calculateNewDirection($direction, $pattern[$i]);
                    $output[] = self::TKN_CUR_LEFT;
                    break;
            }
        }

        return $output;
    }

    private function calculateNewDirection($direction, $symbol)
    {

        $sign = -1;
        if ($direction % 2 == 0) {
            $sign = 1;
        }

        if ($symbol == self::SYM_CUR_LEFT) {
             $sign *= -1;
        }

        $direction += $sign;
        if ($direction < 1) {
            $direction = 4;
        }

        if ($direction > 4) {
            $direction = 1;
        }


        return $direction;           
    }

    private function getStraighToken($direction)
    {
        switch ($direction) {
            case self::UP:
                return self::TKN_STR_UP;
            case self::DOWN:
                return self::TKN_STR_DOWN;
            case self::RIGTH:
                return self::TKN_STR_RIGTH;
            case self::LEFT:
                return self::TKN_STR_LEFT;
        }
    }
}

class Renderer
{
    private $tokenizer;

    public function __construct(Tokenizer $tokenizer)
    {
        $this->tokenizer = $tokenizer;
    }

    private function readFromSTDIN()
    {
        return fgets(STDIN);
    }

    public function render($pattern = null)
    {
        if (!$pattern) {
            $pattern = $this->readFromSTDIN();
        }

        $tokens = $this->tokenizer->tokenize($pattern);
        $chars = $this->renderFromTokens($tokens);
        
        $yBounds = $this->getAxisYMatrixBoundaries($chars);

        ksort($chars);
        print_r($chars);
        foreach ($chars as $key => $yData) {
            echo $this->drawLine($yBounds, $yData). PHP_EOL;
        }
    }

    private function drawLine(Array $yBounds, $data)
    {
        $line = '';
        foreach (range($yBounds[0], $yBounds[1]) as $y) {
            if (!isset($data[$y])) {
                $line .= ' ';
                continue;
            }

            $line .= $data[$y];
        }

        return $line;
    }

    private function renderFromTokens(Array $tokens)
    {
        $x = 0; $y = 0;

        $output = [];
        foreach ($tokens as $token) {
            switch ($token) {
                case Tokenizer::TKN_START:
                    $output[$x][$y] = '#';
                    break;
                case Tokenizer::TKN_STR_LEFT:
                    $y--;
                    $output[$x][$y] = '-';
                    break;
                case Tokenizer::TKN_STR_RIGTH:
                    $y++;
                    $output[$x][$y] = '-';
                    break;
                case Tokenizer::TKN_STR_UP:
                    $x--;
                    $output[$x][$y] = '|';
                    break;
                case Tokenizer::TKN_STR_DOWN:
                    $x++;
                    $output[$x][$y] = '|';
                    break;
                case Tokenizer::TKN_CUR_RIGTH:
                    $output[$x][$y] = '\\';
                    break;
                case Tokenizer::TKN_CUR_LEFT:
                    $output[$x][$y] = '/';
                    break;
            }
        }

        return $output;
    }

    private function getAxisYMatrixBoundaries(Array $chars)
    {
        $yKeys = [];
        foreach ($chars as $yValues) {
            $yKeys = array_merge($yKeys, array_keys($yValues));
        }

        return [min($yKeys), max($yKeys)];
    }
}


$tokenizer = new Tokenizer();
$renderer = new Renderer($tokenizer);
$renderer->render('------\-/-/-\-----#-------\--/----------------\--\----\---/---');
