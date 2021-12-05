<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Functional;

use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Dom\Element;

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
