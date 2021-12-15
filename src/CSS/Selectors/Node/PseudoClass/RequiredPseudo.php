<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node\PseudoClass;

use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\DOM\Element;

/**
 * @see https://html.spec.whatwg.org/multipage/semantics-other.html#selector-required
 */
final class RequiredPseudo extends PseudoClassSelector
{
    public function matches(QueryContext $context, Element $element): bool
    {
        $type = $context->caseInsensitiveTypes ? strtolower($element->localName) : $element->localName;
        return match ($type) {
            'input', 'select', 'textarea' => $element->hasAttribute('required'),
            default => false,
        };
    }
}
