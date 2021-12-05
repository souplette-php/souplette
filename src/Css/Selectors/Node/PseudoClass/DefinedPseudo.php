<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\PseudoClass;

use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Dom\Element;

/**
 * @see https://html.spec.whatwg.org/multipage/semantics-other.html#selector-defined
 */
final class DefinedPseudo extends PseudoClassSelector
{
    public function matches(QueryContext $context, Element $element): bool
    {
        // Since we don't support custom elements, we just make everything defined.
        return true;
    }
}
