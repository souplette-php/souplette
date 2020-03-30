#!/usr/bin/env php
<?php

use JoliPotage\Codegen\EncodingLookupGenerator;

require_once __DIR__.'/../vendor/autoload.php';

$generator = new EncodingLookupGenerator();
$generator->generate();
