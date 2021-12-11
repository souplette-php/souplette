#!/usr/bin/env php
<?php

use Souplette\Html\HtmlParser;
use Souplette\Tests\Html5Lib\DataFile;
use Souplette\Tests\Html5Lib\TreeConstruction\Serializer;

require __DIR__.'/../vendor/autoload.php';

$file = $argv[1] ?? null;
$testno = $argv[2] ?? null;
if ($file === null || $testno === null) {
    echo <<<EOF
Usage: {$argv[0]} <test-file(string)> <test-index(int)>

EOF;
    exit(1);
}

$testFile = new DataFile($file);
$test = $testFile[$testno];
printf("#DATA: %s\n", $test['data']);
printf("#EXPECTED:\n%s\n", $test['document']);

$parser = new HtmlParser();
$doc = $parser->parse($test['data']);

$serializer = new Serializer();
//var_dump($doc->saveHTML());
printf("\n#ACTUAL:\n%s\n", $serializer->serialize($doc));
