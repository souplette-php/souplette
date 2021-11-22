<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\Functional;

use Souplette\Css\Selectors\Query\Helper\TypeMatchHelper;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Dom\ElementIterator;

final class NthOfTypeEvaluator extends AbstractNthEvaluator
{
    protected function getChildIndex(QueryContext $context, \DOMElement $element): int
    {
        $type = $element->localName;
        $index = 1;
        foreach (ElementIterator::preceding($element) as $sibling) {
            if (TypeMatchHelper::isOfType($sibling, $type, $context->caseInsensitiveTypes)) {
                $index++;
            }
        }
        return $index;
    }
}
