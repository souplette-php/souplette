<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Functional;

use Souplette\Css\Selectors\Node\FunctionalSelector;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Selectors\Specificity;

final class Where extends FunctionalSelector
{
    public function __construct(public SelectorList $selectorList)
    {
        parent::__construct('where', [$this->selectorList]);
    }

    public function __toString(): string
    {
        return ":where({$this->selectorList})";
    }

    public function getSpecificity(): Specificity
    {
        return new Specificity(0, 0, 0);
    }

    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        return $this->selectorList->matches($context, $element);
    }
}
