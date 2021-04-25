<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\Simple;

use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\QueryContext;

final class IdEvaluator implements EvaluatorInterface
{
    public function __construct(
        public string $id,
    ) {
    }

    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        $id = $element->getAttribute('id');
        return match($context->caseInsensitiveIds) {
            true => strcasecmp($id, $this->id) === 0,
            false => $this->id === $id,
        };
    }
}
