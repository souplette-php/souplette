<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\PseudoClass;

use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\Helper\TypeMatchHelper;
use Souplette\Css\Selectors\Query\QueryContext;

/**
 * @see https://drafts.csswg.org/selectors-4/#enableddisabled
 * @see https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#concept-fe-disabled
 */
final class DisabledEvaluator implements EvaluatorInterface
{
    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        $type = $context->caseInsensitiveTypes ? strtolower($element->localName) : $element->localName;
        return match ($type) {
            'input', 'button', 'select', 'textarea' => (
                $element->hasAttribute('disabled')
                || self::inDisabledFieldset($element, $context)
            ),
            'fieldset', 'optgroup', 'option' => $element->hasAttribute('disabled'),
            default => false,
        };
    }

    public static function inDisabledFieldset(\DOMElement $element, QueryContext $ctx): bool
    {
        $node = $element;
        $lastLegendAncestor = null;
        while (($node = $node->parentNode) && $node->nodeType === XML_ELEMENT_NODE) {
            if (TypeMatchHelper::isOfType($node, 'legend')) {
                $lastLegendAncestor = $node;
                continue;
            }
            if (
                TypeMatchHelper::isOfType($node, 'fieldset')
                && $node->hasAttribute('disabled')
            ) {
                if ($lastLegendAncestor) {
                    $child = $node->firstElementChild;
                    while ($child) {
                        if (TypeMatchHelper::isOfType($child, 'legend')) {
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
