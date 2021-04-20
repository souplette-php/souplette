<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator;

use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\QueryContext;

final class ListEvaluator implements EvaluatorInterface
{
    /**
     * @param EvaluatorInterface[] $evaluators
     */
    public function __construct(
        public array $evaluators,
    ) {
    }

    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        foreach ($this->evaluators as $evaluator) {
            if ($evaluator->matches($context, $element)) {
                return true;
            }
        }
        return false;
    }
}
