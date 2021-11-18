<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

use Souplette\Css\Selectors\Specificity;
use Souplette\Css\Syntax\SyntaxNode;

abstract class Selector extends SyntaxNode
{
    abstract public function getSpecificity(): Specificity;

    /**
     * Yields this selector's simple selectors, either at top-level or as arguments of a functional selector
     * @return iterable<SimpleSelector>.
     */
    abstract public function simpleSelectors(): iterable;
}
