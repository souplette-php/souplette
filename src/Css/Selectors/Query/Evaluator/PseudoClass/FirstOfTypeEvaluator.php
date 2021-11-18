<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\PseudoClass;

use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\Helper\TypeMatchHelper;
use Souplette\Css\Selectors\Query\QueryContext;

/**
 * @see https://drafts.csswg.org/selectors-4/#the-first-of-type-pseudo
 */
final class FirstOfTypeEvaluator implements EvaluatorInterface
{
    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        $type = $element->localName;
        while ($previous = $element->previousElementSibling) {
            if (TypeMatchHelper::isOfType($previous, $type, $context->caseInsensitiveTypes)) {
                return false;
            }
        }
        return true;
    }
}
