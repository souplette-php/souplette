<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node\Functional;

use Souplette\CSS\Selectors\Node\FunctionalSelector;
use Souplette\CSS\Selectors\Node\SelectorList;
use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\CSS\Selectors\Specificity;
use Souplette\CSS\Syntax\Node\AnPlusB;
use Souplette\DOM\Element;
use Souplette\DOM\Traversal\ElementTraversal;

final class NthLastChild extends FunctionalSelector
{
    use NthFilteredMatcher;

    public ?SelectorList $selectorList = null;

    public function __construct(
        public AnPlusB $anPlusB,
        ?SelectorList $selectorList = null
    ) {
        $this->selectorList = $selectorList;
        $args = [$this->anPlusB];
        if ($this->selectorList) {
            $args[] = $this->selectorList;
        }

        parent::__construct('nth-last-child', $args);
    }

    public function __toString(): string
    {
        return sprintf(
            ':nth-last-child(%s%s)',
            $this->anPlusB,
            $this->selectorList ? " of {$this->selectorList}" : '',
        );
    }

    public function getSpecificity(): Specificity
    {
        $spec = parent::getSpecificity();
        if ($this->selectorList) {
            $spec = $spec->add($this->selectorList->getSpecificity());
        }
        return $spec;
    }

    private function getChildIndex(QueryContext $context, Element $element): int
    {
        $index = 1;
        foreach (ElementTraversal::following($element) as $sibling) {
            if (!$this->selectorList || $this->selectorList->matches($context, $sibling)) {
                $index++;
            }
        }
        return $index;
    }
}
