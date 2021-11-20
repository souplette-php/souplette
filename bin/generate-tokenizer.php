#!/usr/bin/env php
<?php

use Souplette\Codegen\TokenizerGenerator;

require_once __DIR__.'/../vendor/autoload.php';

putenv('PHP_CS_FIXER_IGNORE_ENV=1');

$generator = new TokenizerGenerator();
$generator->generate();
