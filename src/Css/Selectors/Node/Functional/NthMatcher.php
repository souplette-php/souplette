<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Functional;

use Souplette\Css\Selectors\Query\AnPlusBMatcher;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Syntax\Node\AnPlusB;

trait NthMatcher
{
    public AnPlusB $anPlusB;

    abstract private function getChildIndex(QueryContext $context, \DOMElement $element): int;

    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        $index = $this->getChildIndex($context, $element);
        return $this->anPlusB->matchesIndex($index);
    }
}
