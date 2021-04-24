<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\Functional;

use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\QueryContext;

final class NotEvaluator implements EvaluatorInterface
{
    public function __construct(
        private EvaluatorInterface $evaluator,
    ) {
    }

    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        return !$this->evaluator->matches($context, $element);
    }
}
