<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node;

use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\CSS\Selectors\Specificity;
use Souplette\CSS\Syntax\SyntaxNode;
use Souplette\DOM\Element;

abstract class Selector extends SyntaxNode
{
    abstract public function getSpecificity(): Specificity;

    public function matches(QueryContext $context, Element $element): bool
    {
        return false;
    }
}
