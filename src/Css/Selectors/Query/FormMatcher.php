<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query;

final class FormMatcher
{
    public static function inDisabledFieldset(\DOMElement $element, QueryContext $ctx): bool
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
