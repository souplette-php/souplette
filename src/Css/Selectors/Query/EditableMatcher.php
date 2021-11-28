<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query;

final class EditableMatcher
{
    private const STATES = [
        '' => true,
        'true' => true,
        'false' => false,
    ];

    public static function isEditable(\DOMElement $element, QueryContext $ctx): bool
    {
        $state = self::getContentEditableState($element);
        if ($state !== null) return $state;
        for ($node = $element->parentNode; $node && $node->nodeType === XML_ELEMENT_NODE; $node = $node->parentNode) {
            $state = self::getContentEditableState($node);
            if ($state !== null) return $state;
        }
        return false;
    }

    private static function getContentEditableState(\DOMElement $element): ?bool
    {
        if ($element->hasAttribute('contenteditable')) {
            $value = $element->getAttribute('contenteditable');
            return self::STATES[$value] ?? null;
        }
        return null;
    }
}
