<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node\PseudoClass;

use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\CSS\Selectors\Query\TypeMatcher;
use Souplette\DOM\Element;

/**
 * @see https://drafts.csswg.org/selectors-4/#the-only-of-type-pseudo
 */
final class OnlyOfTypePseudo extends PseudoClassSelector
{
    public function matches(QueryContext $context, Element $element): bool
    {
        $type = $element->localName;
        for ($node = $element->previousElementSibling; $node; $node = $node->previousElementSibling) {
            if (TypeMatcher::isOfType($node, $type, $context->caseInsensitiveTypes)) {
                return false;
            }
        }
        for ($node = $element->nextElementSibling; $node; $node = $node->nextElementSibling) {
            if (TypeMatcher::isOfType($node, $type, $context->caseInsensitiveTypes)) {
                return false;
            }
        }
        return true;
    }
}
