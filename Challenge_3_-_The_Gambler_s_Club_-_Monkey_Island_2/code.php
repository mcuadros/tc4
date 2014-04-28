<?php

class GamblerAlgorithm
{
    private $centerPoint = [0, 0];

    public function calculate($x, $y)
    {
        $point = [$x, $y];
        $distance = $this->calcEuclideanDistance($this->centerPoint, $point);

        return round($distance, 2);
    }

    //Based in http://tpc247.blogspot.com.es/2011/03/how-to-use-python-r-and-ruby-to-find.html
    public function calcEuclideanDistance($pointA, $pointB)
    {
        $c = array_map(function($n, $m) {
            return pow($n - $m, 2);
        }, $pointA, $pointB);

        return pow(array_sum($c), .5);
    }
}
    
class Printer
{
    private $algorithm;
   
    public function __construct(GamblerAlgorithm $algorithm)
    {
        $this->algorithm = $algorithm;
    }

    public function process()
    {
        $data = $this->readFromSTDIN();
        $lines = (int) trim(array_shift($data));

        $result = [];
        for($i=0;$i<$lines;$i++) {
            echo $this->calculate($data[$i]) . PHP_EOL;
        }

        return $result;
    }

    private function calculate($line)
    {
        list($x, $y) = explode(' ', trim($line));
        
        return $this->algorithm->calculate($x, $y);
    }

    private function readFromSTDIN()
    {
        $data = [];
        while($line = fgets(STDIN)){
            $data[] = $line;
        }

        return $data;
    }
}

$algorithm = new GamblerAlgorithm();
$printer = new Printer($algorithm);
$printer->process();

