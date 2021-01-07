#!/usr/bin/env php
<?php

use Souplette\Codegen\EntityLookupGenerator;

require_once __DIR__.'/../vendor/autoload.php';

$generator = new EntityLookupGenerator();
$generator->generate();
