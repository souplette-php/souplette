#!/usr/bin/env php
<?php

use ju1ius\HtmlParser\Parser\Parser;
use ju1ius\HtmlParser\Tests\Html5Lib\DataFile;
use ju1ius\HtmlParser\Tests\Html5Lib\Serializer;

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

$parser = new Parser();
$doc = $parser->parse($test['data']);

$serializer = new Serializer();
var_dump($serializer->serialize($doc));
