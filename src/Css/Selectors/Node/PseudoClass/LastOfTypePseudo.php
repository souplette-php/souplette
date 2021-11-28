<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\PseudoClass;

use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Selectors\Query\TypeMatcher;

/**
 * @see https://drafts.csswg.org/selectors-4/#the-last-of-type-pseudo
 */
final class LastOfTypePseudo extends PseudoClassSelector
{
    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        $type = $element->localName;
        for ($next = $element->nextElementSibling; $next; $next = $next->nextElementSibling) {
            if (TypeMatcher::isOfType($next, $type, $context->caseInsensitiveTypes)) {
                return false;
            }
        }
        return true;
    }
}
