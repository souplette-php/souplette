<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node\PseudoClass;

use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\CSS\Selectors\Query\TypeMatcher;
use Souplette\DOM\Element;

/**
 * @see https://drafts.csswg.org/selectors-4/#the-first-of-type-pseudo
 */
final class FirstOfTypePseudo extends PseudoClassSelector
{
    public function matches(QueryContext $context, Element $element): bool
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
