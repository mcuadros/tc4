<?php

class DNACombinator 
{
    const N_ADENINE = 'A';
    const N_CYTOSINE = 'C';
    const N_GUANINE = 'G';
    const N_THYMINE = 'T';

    private $map = [
        self::N_ADENINE,
        self::N_CYTOSINE,
        self::N_GUANINE,
        self::N_THYMINE
    ];

    public function __construct()
    {
        $this->rmap = array_flip($this->map);
    }

    public function calculate($input, $output, Array $options)
    {
        $options = array_flip($options);
        $length = strlen($input);

        $path = [];
        $current = $input;
        while($current) {
            $current = $this->findNextValidStep($length, $current, $options);
            $path[] = $current;

            if ($current == $output) {
                break;
            }
        }

        return $path;
    }

    private function findNextValidStep($length, $current, Array &$options)
    {
        $nucleotide = 0;
        $position = 0;
        while(1) {
            $next = $this->rotatePosition($current, $position, $nucleotide++);

            if ($nucleotide > 3) {
                if ($position >= $length) return null;

                $nucleotide = 0;
                $position++;
            }

            if ($this->isValid($next, $options)) {
                break;
            }
        };

        unset($options[$next]);

        return $next;
    }

    private function isValid($current, Array $options)
    {
        if (!isset($options[$current])) {
            return null;
        }

        return true;
    }

    private function rotatePosition($current, $position, $nucleotide)
    {
        $current[$position] = $this->map[$nucleotide];

        return $current;
    }
}
    
class Printer
{
    const PATH_SEPARATOR = '->';

    private $combinator;
   
    public function __construct(DNACombinator $combinator)
    {
        $this->combinator = $combinator;
    }

    public function process($filename = null)
    {
        if ($filename) {
            $data = file($filename);
        } else {
            $data = $this->readFromSTDIN();
        }

        $data = $this->removeEOL($data);
    
        $input = array_shift($data);
        $output = array_shift($data);
       
        $paths = $this->combinator->calculate($input, $output, $data);
        $this->printResult($paths);
    }
    
    private function readFromSTDIN()
    {
        $data = [];
        while($line = fgets(STDIN)){
            $data[] = $line;
        }

        return $data;
    }

    private function removeEOL(Array $data)
    {
        return array_map('trim', $data);
    }

    private function printResult(Array $paths)
    {
        echo $this->formatResult($paths);
    }

    private function formatResult(Array $paths)
    {
        return implode(self::PATH_SEPARATOR, $paths);
    }
}

$combinator = new DNACombinator();
$printer = new Printer($combinator);
$printer->process();
