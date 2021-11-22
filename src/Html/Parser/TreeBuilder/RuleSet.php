<?php declare(strict_types=1);

namespace Souplette\Html\Parser\TreeBuilder;

use Souplette\Html\Tokenizer\Token;

abstract class RuleSet
{
    final private function __construct() {}

    abstract public static function process(Token $token, TreeBuilder $tree);
}
