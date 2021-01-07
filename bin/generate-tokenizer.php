#!/usr/bin/env php
<?php

use Souplette\Codegen\TokenizerGenerator;

require_once __DIR__.'/../vendor/autoload.php';

$generator = new TokenizerGenerator();
$generator->generate();
