<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\PseudoClass;

use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Selectors\Query\TypeMatcher;

/**
 * @see https://drafts.csswg.org/selectors-4/#the-first-of-type-pseudo
 */
final class FirstOfTypePseudo extends PseudoClassSelector
{
    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        $type = $element->localName;
        for ($prev = $element->previousElementSibling; $prev; $prev = $prev->previousElementSibling) {
            if (TypeMatcher::isOfType($prev, $type, $context->caseInsensitiveTypes)) {
                return false;
            }
        }
        return true;
    }
}
