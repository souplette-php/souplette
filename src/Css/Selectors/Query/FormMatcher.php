<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query;

final class FormMatcher
{
    public static function isEnabled(\DOMElement $element, QueryContext $ctx): bool
    {
        return !self::isDisabled($element, $ctx);
    }

    public static function isDisabled(\DOMElement $element, QueryContext $ctx): bool
    {
        $caseInsensitive = $ctx->caseInsensitiveTypes;
        $type = $caseInsensitive ? strtolower($element->localName) : $element->localName;
        switch ($type) {
            case 'fieldset':
                // https://html.spec.whatwg.org/multipage/form-elements.html#concept-fieldset-disabled
            case 'button':
            case 'input':
            case 'select':
            case 'textarea':
                // https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#concept-fe-disabled
                return $element->hasAttribute('disabled') || self::inDisabledFieldset($element, $ctx);
            case 'optgroup':
                return $element->hasAttribute('disabled');
            case 'option':
                // https://html.spec.whatwg.org/multipage/form-elements.html#concept-option-disabled
                if ($element->hasAttribute('disabled')) return true;
                $parent = $element->parentNode;
                return (
                    $parent
                    && TypeMatcher::isOfType($parent, 'optgroup', $caseInsensitive)
                    && $parent->hasAttribute('disabled')
                );
            default:
                return false;
        }
    }

    public static function isReadOnly(\DOMElement $element, QueryContext $ctx): bool
    {
        return !self::isReadWrite($element, $ctx);
    }

    public static function isReadWrite(\DOMElement $element, QueryContext $ctx): bool
    {
        $caseInsensitive = $ctx->caseInsensitiveTypes;
        $type = $caseInsensitive ? strtolower($element->localName) : $element->localName;
        return match ($type) {
            'input', 'textarea' => !$element->hasAttribute('readonly') && !self::isDisabled($element, $ctx),
            default => EditableMatcher::isEditable($element, $ctx),
        };
    }

    private static function inDisabledFieldset(\DOMElement $element, QueryContext $ctx): bool
    {
        $node = $element;
        $lastLegendAncestor = null;
        $caseInsensitive = $ctx->caseInsensitiveTypes;
        while (($node = $node->parentNode) && $node->nodeType === XML_ELEMENT_NODE) {
            if (TypeMatcher::isOfType($node, 'legend', $caseInsensitive)) {
                $lastLegendAncestor = $node;
                continue;
            }
            if (
                TypeMatcher::isOfType($node, 'fieldset', $caseInsensitive)
                && $node->hasAttribute('disabled')
            ) {
                if ($lastLegendAncestor) {
                    $child = $node->firstElementChild;
                    while ($child) {
                        if (TypeMatcher::isOfType($child, 'legend', $caseInsensitive)) {
                            if ($child === $lastLegendAncestor) {
                                return false;
                            }
                            break;
                        }
                        $child = $child->nextElementSibling;
                    }
                }

                return true;
            }
        }
        return false;
    }
}
