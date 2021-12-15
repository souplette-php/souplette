<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node\PseudoClass;

use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\CSS\Selectors\Query\FormMatcher;
use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\DOM\Element;

final class ReadWritePseudo extends PseudoClassSelector
{
    public function matches(QueryContext $context, Element $element): bool
    {
        return FormMatcher::isReadWrite($element, $context);
    }
}
