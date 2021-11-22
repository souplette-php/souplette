<?php declare(strict_types=1);

namespace Souplette\Html\TreeBuilder;

use Souplette\Html\Tokenizer\Token;
use Souplette\Html\TreeBuilder;

abstract class RuleSet
{
    final private function __construct() {}

    abstract public static function process(Token $token, TreeBuilder $tree);
}
