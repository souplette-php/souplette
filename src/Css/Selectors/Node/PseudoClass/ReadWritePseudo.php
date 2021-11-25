<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\PseudoClass;

use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Css\Selectors\Query\QueryContext;

final class ReadWritePseudo extends PseudoClassSelector
{
    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        $type = $context->caseInsensitiveTypes ? strtolower($element->localName) : $element->localName;
        return match ($type) {
            'input', 'textarea' => (
                !$element->hasAttribute('readonly')
                && !$element->hasAttribute('disabled')
            ),
            default => $element->getAttribute('contenteditable') === 'true',
        };
    }
}