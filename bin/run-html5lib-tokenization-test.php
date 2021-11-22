#!/usr/bin/env php
<?php

use Souplette\Html\Tokenizer\Tokenizer;
use Souplette\Tests\Html5Lib\JsonFile;

require __DIR__.'/../vendor/autoload.php';

$file = $argv[1] ?? null;
$testno = $argv[2] ?? null;
if ($file === null || $testno === null) {
    echo <<<EOF
Usage: {$argv[0]} <test-file(string)> <test-index(int)>

EOF;
    exit(1);
}

$testFile = new JsonFile($file);
$test = $testFile[$testno];

$tokenizer = new Tokenizer($test['input']);
$tokens = iterator_to_array($tokenizer->tokenize());

var_dump($tokens);
