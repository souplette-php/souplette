<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\PseudoClass;

use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Css\Selectors\Query\QueryContext;

/**
 * @see https://drafts.csswg.org/selectors-4/#the-scope-pseudo
 */
final class ScopePseudo extends PseudoClassSelector
{
    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        if (!$context->scopingRoot) return false;
        if ($context->scopingRoot === $element->ownerDocument) {
            return $element === $element->ownerDocument->documentElement;
        }
        return $element === $context->scopingRoot;
    }
}
