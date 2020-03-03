<?php

use ju1ius\HtmlParser\Codegen\TokenizerGenerator;

require_once __DIR__.'/../vendor/autoload.php';

$generator = new TokenizerGenerator();
$generator->generate();

$rules = [
    '@PSR2' => true,
    'no_extra_blank_lines' => ['extra', 'curly_brace_block'],
];
$cmd = sprintf(
    '%s fix --quiet --using-cache=no --rules=%s %s',
    realpath(__DIR__.'/../tools/php-cs-fixer'),
    sprintf("'%s'", json_encode($rules)),
    realpath(__DIR__.'/../src/Parser/Tokenizer.php')
);
//echo $cmd . PHP_EOL;
passthru($cmd);
