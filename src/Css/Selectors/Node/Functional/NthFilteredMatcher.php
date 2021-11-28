<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Functional;

use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Query\AnPlusBMatcher;
use Souplette\Css\Selectors\Query\QueryContext;

trait NthFilteredMatcher
{
    use NthMatcher;

    public ?SelectorList $selectorList = null;

    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        if ($this->selectorList && !$this->selectorList->matches($context, $element)) {
            return false;
        }
        $index = $this->getChildIndex($context, $element);
        return $this->anPlusB->matchesIndex($index);
    }
}
