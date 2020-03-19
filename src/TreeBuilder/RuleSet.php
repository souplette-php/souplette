<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder;

use ju1ius\HtmlParser\Tokenizer\Token;

abstract class RuleSet
{
    final private function __construct() {}

    abstract public static function process(Token $token, TreeBuilder $tree);
}
