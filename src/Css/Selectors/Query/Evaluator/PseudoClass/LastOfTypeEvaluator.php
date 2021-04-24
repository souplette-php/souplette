<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\PseudoClass;

use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\Helper\TypeMatchHelper;
use Souplette\Css\Selectors\Query\QueryContext;

/**
 * @see https://drafts.csswg.org/selectors-4/#the-last-of-type-pseudo
 */
final class LastOfTypeEvaluator implements EvaluatorInterface
{
    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        $type = $element->localName;
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
