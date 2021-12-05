<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Functional;

use Souplette\Css\Selectors\Node\FunctionalSelector;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Selectors\Query\TypeMatcher;
use Souplette\Css\Syntax\Node\AnPlusB;
use Souplette\Dom\Element;
use Souplette\Dom\Traversal\ElementTraversal;

final class NthLastOfType extends FunctionalSelector
{
    use NthMatcher;

    public function __construct(
        public AnPlusB $anPlusB,
    ) {
        parent::__construct('nth-last-of-type', [$anPlusB]);
    }

    public function __toString(): string
    {
        return ":nth-last-of-type({$this->anPlusB})";
    }

    protected function getChildIndex(QueryContext $context, Element $element): int
    {
        $type = $element->localName;
        $index = 1;
        foreach (ElementTraversal::following($element) as $sibling) {
            if (TypeMatcher::isOfType($sibling, $type, $context->caseInsensitiveTypes)) {
                $index++;
            }
        }
        return $index;
    }
}
