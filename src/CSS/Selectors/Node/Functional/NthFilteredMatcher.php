<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node\Functional;

use Souplette\CSS\Selectors\Node\SelectorList;
use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\DOM\Element;

trait NthFilteredMatcher
{
    use NthMatcher;

    public ?SelectorList $selectorList = null;

    public function matches(QueryContext $context, Element $element): bool
    {
        if ($this->selectorList && !$this->selectorList->matches($context, $element)) {
            return false;
        }
        $index = $this->getChildIndex($context, $element);
        return $this->anPlusB->matchesIndex($index);
    }
}
