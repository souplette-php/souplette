<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\PseudoClass;

use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Css\Selectors\Query\FormMatcher;
use Souplette\Css\Selectors\Query\QueryContext;

final class ReadOnlyPseudo extends PseudoClassSelector
{
    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        return FormMatcher::isReadOnly($element, $context);
    }
}
