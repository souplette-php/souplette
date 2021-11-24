<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\PseudoClass;

use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Selectors\Query\TypeMatcher;

/**
 * @see https://drafts.csswg.org/selectors-4/#the-only-of-type-pseudo
 */
final class OnlyOfTypePseudo extends PseudoClassSelector
{
    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        $type = $element->localName;
        $previous = $element->previousElementSibling;
        while ($previous) {
            if (TypeMatcher::isOfType($previous, $type, $context->caseInsensitiveTypes)) {
                return false;
            }
            $previous = $previous->previousElementSibling;
        }
        $next = $element->nextElementSibling;
        while ($next) {
            if (TypeMatcher::isOfType($next, $type, $context->caseInsensitiveTypes)) {
                return false;
            }
            $next = $next->nextElementSibling;
        }
        return true;
    }
}
