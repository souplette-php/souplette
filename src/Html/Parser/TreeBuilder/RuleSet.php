<?php declare(strict_types=1);

namespace JoliPotage\Html\Parser\TreeBuilder;

use JoliPotage\Html\Parser\Tokenizer\Token;

abstract class RuleSet
{
    final private function __construct() {}

    abstract public static function process(Token $token, TreeBuilder $tree);
}
