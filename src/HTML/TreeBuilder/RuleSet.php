<?php declare(strict_types=1);

namespace Souplette\HTML\TreeBuilder;

use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\TreeBuilder;

abstract class RuleSet
{
    final private function __construct() {}

    abstract public static function process(Token $token, TreeBuilder $tree);
}
