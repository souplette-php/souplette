<?php

use ju1ius\HtmlParser\Codegen\EncodingLookupGenerator;

require_once __DIR__.'/../vendor/autoload.php';

$generator = new EncodingLookupGenerator();
$generator->generate();
