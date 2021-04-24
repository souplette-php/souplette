<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\PseudoClass;

use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\Helper\TypeMatchHelper;
use Souplette\Css\Selectors\Query\QueryContext;

/**
 * @see https://drafts.csswg.org/selectors-4/#the-only-of-type-pseudo
 */
final class OnlyOfTypeEvaluator implements EvaluatorInterface
{
    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        $type = $element->localName;
        $previous = $element->previousElementSibling;
        while ($previous) {
            if (TypeMatchHelper::isOfType($previous, $type, $context->caseInsensitiveTypes)) {
                return false;
            }
            $previous = $previous->previousElementSibling;
        }
        $next = $element->nextElementSibling;
        while ($next) {
            if (TypeMatchHelper::isOfType($next, $type, $context->caseInsensitiveTypes)) {
                return false;
            }
            $next = $next->nextElementSibling;
        }
        return true;
    }
}
