<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\PseudoClass;

use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\QueryContext;

/**
 * @see https://drafts.csswg.org/selectors-4/#enableddisabled
 */
final class EnabledEvaluator implements EvaluatorInterface
{
    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        $type = $context->caseInsensitiveTypes ? strtolower($element->localName) : $element->localName;
        return match ($type) {
            'input', 'button', 'select', 'textarea' => (
                !$element->hasAttribute('disabled')
                && !DisabledEvaluator::inDisabledFieldset($element, $context)
            ),
            'fieldset', 'optgroup', 'option' => !$element->hasAttribute('disabled'),
            default => false,
        };
    }
}
