<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator;

use Souplette\Css\Selectors\Node\Functional\NthChild;
use Souplette\Css\Selectors\Node\Functional\NthLastChild;
use Souplette\Css\Selectors\Node\Functional\NthLastOfType;
use Souplette\Css\Selectors\Node\Functional\NthOfType;
use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\Helper\NthChildMatchHelper;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Syntax\Node\AnPlusB;

final class NthChildEvaluator implements EvaluatorInterface
{
    public function matches(QueryContext $context): bool
    {
        $selector = $context->selector;
        assert(
            $selector instanceof NthChild
            || $selector instanceof NthLastChild
            || $selector instanceof NthOfType
            || $selector instanceof NthLastOfType
        );
        /** @var AnPlusB $anPlusB */
        $anPlusB = $selector->anPlusB;

        $sameType = match ($selector::class) {
            NthOfType::class, NthLastOfType::class => true,
            default => false,
        };
        $fromEnd = match ($selector::class) {
            NthLastChild::class, NthLastOfType::class => true,
            default => false,
        };
        // TODO: handle :nth-child(An+B of <selector-list>)
        return NthChildMatchHelper::matchesNthChild(
            $context->element,
            $anPlusB->a,
            $anPlusB->b,
            $sameType,
            $fromEnd,
        );
    }
}
