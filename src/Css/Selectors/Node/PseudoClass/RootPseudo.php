<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\PseudoClass;

use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Css\Selectors\Query\QueryContext;

/**
 * @see https://drafts.csswg.org/selectors-4/#the-root-pseudo
 */
final class RootPseudo extends PseudoClassSelector
{
    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        return $element === $context->document->documentElement;
    }
}
