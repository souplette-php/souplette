<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Selectors\Specificity;
use Souplette\Css\Syntax\SyntaxNode;

abstract class Selector extends SyntaxNode
{
    abstract public function getSpecificity(): Specificity;

    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        return false;
    }
}
