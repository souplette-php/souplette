<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\Functional;

use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Html\Dom\ElementIterator;

/**
 * @see https://drafts.csswg.org/selectors-4/#relational
 */
final class HasEvaluator implements EvaluatorInterface
{
    public function __construct(
        private EvaluatorInterface $evaluator,
    ) {
    }

    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        $subContext = $context->withScope($element);
        foreach (ElementIterator::descendants($element) as $candidate) {
            if ($this->evaluator->matches($subContext, $candidate)) {
                return true;
            }
        }
        return false;
    }
}
