<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\Functional;

use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\Helper\NthChildMatchHelper;
use Souplette\Css\Selectors\Query\QueryContext;

final class NthChildEvaluator implements EvaluatorInterface
{
    public function __construct(
        public int $a,
        public int $b,
    ) {
    }

    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        //$sameType = match ($selector::class) {
        //    NthOfType::class, NthLastOfType::class => true,
        //    default => false,
        //};
        //$fromEnd = match ($selector::class) {
        //    NthLastChild::class, NthLastOfType::class => true,
        //    default => false,
        //};
        // TODO: handle :nth-child(An+B of <selector-list>)
        return NthChildMatchHelper::matchesNthChild(
            $element,
            $this->a,
            $this->b,
            false,
            false,
        );
    }
}
