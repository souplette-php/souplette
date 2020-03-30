#!/usr/bin/env php
<?php

use JoliPotage\Codegen\EntityLookupGenerator;

require_once __DIR__.'/../vendor/autoload.php';

$generator = new EntityLookupGenerator();
$generator->generate();
