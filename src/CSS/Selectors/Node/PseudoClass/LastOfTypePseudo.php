<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node\PseudoClass;

use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\CSS\Selectors\Query\TypeMatcher;
use Souplette\DOM\Element;

/**
 * @see https://drafts.csswg.org/selectors-4/#the-last-of-type-pseudo
 */
final class LastOfTypePseudo extends PseudoClassSelector
{
    public function matches(QueryContext $context, Element $element): bool
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
