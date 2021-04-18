<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator;

use Souplette\Css\Selectors\Node\IdSelector;
use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\QueryContext;

final class IdEvaluator implements EvaluatorInterface
{
    public function matches(QueryContext $context): bool
    {
        $selector = $context->selector;
        assert($selector instanceof IdSelector);

        $id = $context->element->getAttribute('id');
        return match($context->caseInsensitiveIds) {
            true => strcasecmp($id, $selector->id) === 0,
            false => $selector->id === $id,
        };
    }
}
