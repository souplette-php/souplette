<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\PseudoClass;

use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\QueryContext;

/**
 * @see https://drafts.csswg.org/selectors-4/#the-only-child-pseudo
 */
final class OnlyChildEvaluator implements EvaluatorInterface
{
    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        return !$element->previousElementSibling && !$element->nextElementSibling;
    }
}
