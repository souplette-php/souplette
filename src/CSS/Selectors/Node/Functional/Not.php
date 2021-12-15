<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node\Functional;

use Souplette\CSS\Selectors\Node\FunctionalSelector;
use Souplette\CSS\Selectors\Node\SelectorList;
use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\CSS\Selectors\Specificity;
use Souplette\DOM\Element;

final class Not extends FunctionalSelector
{
    public function __construct(public SelectorList $selectorList)
    {
        parent::__construct('not', [$this->selectorList]);
    }

    public function __toString(): string
    {
        return ":not({$this->selectorList})";
    }

    public function getSpecificity(): Specificity
    {
        return $this->selectorList->getSpecificity();
    }

    public function matches(QueryContext $context, Element $element): bool
    {
        return !$this->selectorList->matches($context, $element);
    }
}
