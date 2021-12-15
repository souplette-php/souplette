<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node\Functional;

use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\CSS\Syntax\Node\AnPlusB;
use Souplette\DOM\Element;

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
