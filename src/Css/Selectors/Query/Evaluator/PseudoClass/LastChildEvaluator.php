<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\PseudoClass;

use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\QueryContext;

/**
 * @see https://drafts.csswg.org/selectors-4/#the-last-child-pseudo
 */
final class LastChildEvaluator implements EvaluatorInterface
{
    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        return $element->nextElementSibling === null;
    }
}
