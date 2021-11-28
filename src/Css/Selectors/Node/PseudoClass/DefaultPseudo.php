<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\PseudoClass;

use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Css\Selectors\Query\QueryContext;

/**
 * @see https://html.spec.whatwg.org/multipage/semantics-other.html#selector-default
 */
final class DefaultPseudo extends PseudoClassSelector
{
    private const INPUT_TYPES = [
        'checkbox' => true,
        'radio' => true,
    ];

    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        $type = $context->caseInsensitiveTypes ? strtolower($element->localName) : $element->localName;
        return match ($type) {
            'input' => $element->hasAttribute('checked') && isset(
                self::INPUT_TYPES[strtolower($element->getAttribute('type'))]
            ),
            'option' => $element->hasAttribute('selected'),
            default => false,
        };
    }
}
