#!/usr/bin/env php
<?php declare(strict_types=1);

use Souplette\Codegen\EntityLookupGenerator;
use Souplette\Codegen\HtmlDtdGenerator;

require_once __DIR__ . '/../vendor/autoload.php';

$generator = new EntityLookupGenerator();
$generator->generate();

$generator = new HtmlDtdGenerator();
$generator->generate();
