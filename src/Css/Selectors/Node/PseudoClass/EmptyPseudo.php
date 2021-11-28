<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\PseudoClass;

use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Css\Selectors\Query\QueryContext;

/**
 * @see https://drafts.csswg.org/selectors-4/#empty-pseudo
 */
final class EmptyPseudo extends PseudoClassSelector
{
    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        if ($element->firstElementChild) return false;

        foreach ($element->childNodes as $child) {
            $isEmpty = match ($child->nodeType) {
                XML_ELEMENT_NODE,
                XML_ENTITY_REF_NODE
                    => false,
                XML_TEXT_NODE,
                XML_CDATA_SECTION_NODE
                    => $child->isWhitespaceInElementContent(),
                default => true,
            };
            if (!$isEmpty) return false;
        }
        return true;
    }
}
