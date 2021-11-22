<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\Functional;

use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Dom\ElementIterator;

final class NthLastChildEvaluator extends AbstractNthEvaluator
{
    protected function getChildIndex(QueryContext $context, \DOMElement $element): int
    {
        $index = 1;
        foreach (ElementIterator::following($element) as $sibling) {
            if (!$this->filter || $this->filter->matches($context, $sibling)) {
                $index++;
            }
        }
        return $index;
    }
}
