<?php

class UserStack
{
    private $stack = [];

    public function initialize($filename)
    {
        $data = file($filename);
        foreach($data as $line) {
            $this->processLine($line);
        }

        $this->sortStack();
    }

    private function processLine($line)
    {
        $user = $this->convertLineToArray($line);
        $name = array_shift($user);
        $pattern = $this->buildMatchPattern($user);

        $this->stack[$pattern][] = $name;
    }

    private function convertLineToArray($line)
    {
        $args = explode(',', trim($line));

        return $args;
    }

    private function sortStack()
    {
        foreach ($this->stack as &$names) {
            natsort($names);
        }
    }

    public function findByPool(Array $pool)
    {
        $pattern = $this->buildMatchPattern($pool);

        if (!isset($this->stack[$pattern])) {
            return null;
        }

        return $this->stack[$pattern];
    }

    private function buildMatchPattern(Array $user)
    {
        return implode('_', $user);
    }
}

class UnAnonymizer
{
    private $userStack;

    public function __construct(UserStack $userStack)
    {
        $this->userStack = $userStack;
    }

    private function readFromSTDIN()
    {
        $data = [];
        while($line = fgets(STDIN)){
            $data[] = $line;
        }

        return $data;
    }

    public function process($filename = null)
    {
        if ($filename) {
            $data = file($filename);
        } else {
            $data = $this->readFromSTDIN();
        }

        $lines = (int) trim(array_shift($data));

        $result = [];
        for($i=0;$i<$lines;$i++) {
            $pool = $this->convertLineToArray($data[$i]);
            $result[] = $this->userStack->findByPool($pool);
        }

        return $result;
    }

    private function convertLineToArray($line)
    {
        $args = explode(',', trim($line));

        return $args;
    }
}

class Printer
{
    const NO_MATCH_STRING = 'NONE';
    const NAME_SEPARATOR = ',';

    public function output(Array $results)
    {
        foreach ($results as $number => $result) {
            echo $this->formatResult($number, $result) . PHP_EOL;
        }
    }

    private function formatResult($number, Array $names = null)
    {
       return sprintf(
            'Case #%d: %s',
            $number + 1,
            $this->formatNames($names)
        );
    }

    private function formatNames(Array $names = null)
    {
        if (!$names) {
            return self::NO_MATCH_STRING;
        }

        return implode(self::NAME_SEPARATOR, $names);
    }
}

$userStack = new UserStack();
$userStack->initialize(__DIR__ .'/data/students');

$unAnonymizer = new UnAnonymizer($userStack);
$result = $unAnonymizer->process();

$printer = new Printer();
$printer->output($result);


