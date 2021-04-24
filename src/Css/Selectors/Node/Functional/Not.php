<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Functional;

use Souplette\Css\Selectors\Node\FunctionalSelector;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Specificity;

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
}
