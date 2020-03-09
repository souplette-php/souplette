<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder;

use ju1ius\HtmlParser\Tokenizer\Token;

abstract class RuleSet
{
    abstract public function process(Token $token, TreeBuilder $tree);
}
