<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Selectors\Specificity;
use Souplette\Css\Syntax\SyntaxNode;
use Souplette\Dom\Element;

abstract class Selector extends SyntaxNode
{
    abstract public function getSpecificity(): Specificity;

    public function matches(QueryContext $context, Element $element): bool
    {
        return false;
    }
}
