<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\PseudoClass;

use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Css\Selectors\Query\FormMatcher;
use Souplette\Css\Selectors\Query\QueryContext;

/**
 * @see https://drafts.csswg.org/selectors-4/#enableddisabled
 */
final class EnabledPseudo extends PseudoClassSelector
{
    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        $type = $context->caseInsensitiveTypes ? strtolower($element->localName) : $element->localName;
        return match ($type) {
            'input', 'button', 'select', 'textarea' => (
                !$element->hasAttribute('disabled')
                && !FormMatcher::inDisabledFieldset($element, $context)
            ),
            'fieldset', 'optgroup', 'option' => !$element->hasAttribute('disabled'),
            default => false,
        };
    }
}
