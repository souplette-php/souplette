#!/usr/bin/env php
<?php

use ju1ius\HtmlParser\Codegen\EntityLookupGenerator;

require_once __DIR__.'/../vendor/autoload.php';

$generator = new EntityLookupGenerator();
$generator->generate();
