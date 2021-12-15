<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node\Functional;

use Souplette\CSS\Selectors\Node\FunctionalSelector;
use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\CSS\Selectors\Query\TypeMatcher;
use Souplette\CSS\Syntax\Node\AnPlusB;
use Souplette\DOM\Element;
use Souplette\DOM\Traversal\ElementTraversal;

final class NthOfType extends FunctionalSelector
{
    use NthMatcher;

    public function __construct(
        public AnPlusB $anPlusB
    ) {
        parent::__construct('nth-of-type', [$anPlusB]);
    }

    public function __toString(): string
    {
        return ":nth-of-type({$this->anPlusB})";
    }

    private function getChildIndex(QueryContext $context, Element $element): int
    {
        $type = $element->localName;
        $index = 1;
        foreach (ElementTraversal::preceding($element) as $sibling) {
            if (TypeMatcher::isOfType($sibling, $type, $context->caseInsensitiveTypes)) {
                $index++;
            }
        }
        return $index;
    }
}
