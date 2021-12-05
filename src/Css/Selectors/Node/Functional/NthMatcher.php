<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Functional;

use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Syntax\Node\AnPlusB;
use Souplette\Dom\Element;

trait NthMatcher
{
    public AnPlusB $anPlusB;

    abstract private function getChildIndex(QueryContext $context, Element $element): int;

    public function matches(QueryContext $context, Element $element): bool
    {
        $index = $this->getChildIndex($context, $element);
        return $this->anPlusB->matchesIndex($index);
    }
}
