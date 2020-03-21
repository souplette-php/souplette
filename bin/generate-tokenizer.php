#!/usr/bin/env php
<?php

use ju1ius\HtmlParser\Codegen\TokenizerGenerator;

require_once __DIR__.'/../vendor/autoload.php';

$generator = new TokenizerGenerator();
$generator->generate();
