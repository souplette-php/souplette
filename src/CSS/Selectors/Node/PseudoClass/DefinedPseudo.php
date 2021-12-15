<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node\PseudoClass;

use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\DOM\Element;

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
