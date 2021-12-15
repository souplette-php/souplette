<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node\PseudoClass;

use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\DOM\Element;

/**
 * @see https://drafts.csswg.org/selectors-4/#the-last-child-pseudo
 */
final class LastChildPseudo extends PseudoClassSelector
{
    public function matches(QueryContext $context, Element $element): bool
    {
        return $element->nextElementSibling === null;
    }
}
