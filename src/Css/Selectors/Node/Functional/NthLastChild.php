<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Functional;

use Souplette\Css\Selectors\Node\FunctionalSelector;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Selectors\Specificity;
use Souplette\Css\Syntax\Node\AnPlusB;
use Souplette\Dom\ElementIterator;

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

    private function getChildIndex(QueryContext $context, \DOMElement $element): int
    {
        $index = 1;
        foreach (ElementIterator::following($element) as $sibling) {
            if (!$this->selectorList || $this->selectorList->matches($context, $sibling)) {
                $index++;
            }
        }
        return $index;
    }
}
