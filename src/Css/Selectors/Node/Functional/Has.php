<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Functional;

use Souplette\Css\Selectors\Node\FunctionalSelector;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Specificity;

final class Has extends FunctionalSelector
{
    public function __construct(public SelectorList $selectorList)
    {
        parent::__construct('has', [$this->selectorList]);
    }

    public function simpleSelectors(): \Generator
    {
        yield $this;
        yield from $this->selectorList;
    }

    public function __toString(): string
    {
        return ":has({$this->selectorList})";
    }

    public function getSpecificity(): Specificity
    {
        return $this->selectorList->getSpecificity();
    }
}
