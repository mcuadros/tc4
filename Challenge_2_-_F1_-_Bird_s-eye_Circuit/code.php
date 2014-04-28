<?php

class Tokenizer
{
    const TKN_START = 1;
    const TKN_STR = 10;
    const TKN_CUR_RIGTH = 20;
    const TKN_CUR_LEFT = 21;

    const SYM_START = '#';
    const SYM_CUR_LEFT = '/';
    const SYM_CUR_RIGTH = '\\';
    const SYM_STRAIGHT = '-';

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
            switch ($pattern[$i]) {
                case self::SYM_START:
                    $output[] = self::TKN_START;
                    break;
                case self::SYM_STRAIGHT:
                    $output[] = self::TKN_STR;
                    break;
                case self::SYM_CUR_RIGTH:
                    $output[] = self::TKN_CUR_RIGTH;
                    break;
                case self::SYM_CUR_LEFT:
                    $output[] = self::TKN_CUR_LEFT;
                    break;
            }
        }

        return $output;
    }
}

class Renderer
{
    const UP = 1;
    const RIGTH = 2;
    const DOWN = 3;
    const LEFT = 4;

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
        $chars = $this->calculateMatrix($tokens);
        
        $yBounds = $this->getAxisYMatrixBoundaries($chars);

        ksort($chars);
        foreach ($chars as $key => $yData) {
            echo $this->drawLine($yBounds, $yData). PHP_EOL;
        }
    }

    private function calculateMatrix(Array $tokens)
    {
        $x = 0; $y = 0;
        $direction = self::RIGTH;

        $output = [];
        foreach ($tokens as $token) {
            switch ($token) {
                case Tokenizer::TKN_START:
                    $output[$x][$y] = '#';
                    break;
                case Tokenizer::TKN_STR:
                    $output[$x][$y] = $this->getStraighSymbol($direction);
                    break;
                case Tokenizer::TKN_CUR_RIGTH:
                    $direction = $this->calculateNewDirection($direction, $token);
                    $output[$x][$y] = '\\';
                    break;
                case Tokenizer::TKN_CUR_LEFT:
                    $direction = $this->calculateNewDirection($direction, $token);
                    $output[$x][$y] = '/';
                    break;
            }

            $this->updateAxis($direction, $x, $y);
        }

        return $output;
    }

    private function calculateNewDirection($direction, $symbol)
    {
        $sign = -1;
        if ($direction % 2 == 0) {
            $sign = 1;
        }

        if ($symbol == Tokenizer::TKN_CUR_LEFT) {
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

    private function updateAxis($direction, &$x, &$y)
    {
        switch ($direction) {
            case self::UP:
                $x--;
                break;
            case self::DOWN:
                $x++;
                break;
            case self::RIGTH:
                $y++;
                break;
            case self::LEFT:
                $y--;
                break;
        }
    }

    private function getStraighSymbol($direction)
    {
        switch ($direction) {
            case self::UP:
            case self::DOWN:
                return '|';
            case self::RIGTH:
            case self::LEFT:
                return '-';
        }
    }

    private function getAxisYMatrixBoundaries(Array $chars)
    {
        $yKeys = [];
        foreach ($chars as $yValues) {
            $yKeys = array_merge($yKeys, array_keys($yValues));
        }

        return [min($yKeys), max($yKeys)];
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
}

$tokenizer = new Tokenizer();
$renderer = new Renderer($tokenizer);
$renderer->render();
