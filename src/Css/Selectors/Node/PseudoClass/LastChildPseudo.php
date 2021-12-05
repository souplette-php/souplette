<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\PseudoClass;

use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Dom\Element;

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
