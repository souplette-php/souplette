<?php

use ju1ius\HtmlParser\Codegen\EntityLookupGenerator;

require_once __DIR__.'/../vendor/autoload.php';

$generator = new EntityLookupGenerator();
$generator->generate();

$cmd = sprintf(
    '%s fix --quiet --using-cache=no --rules=@PSR2,array_indentation %s',
    realpath(__DIR__.'/../tools/php-cs-fixer'),
    realpath(__DIR__.'/../src/Parser/Entities/EntityLookup.php')
);
//echo $cmd . PHP_EOL;
passthru($cmd);
