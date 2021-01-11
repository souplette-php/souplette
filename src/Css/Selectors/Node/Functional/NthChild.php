<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Functional;

use Souplette\Css\Selectors\Node\FunctionalSelector;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Specificity;
use Souplette\Css\Syntax\Node\AnPlusB;

final class NthChild extends FunctionalSelector
{
    public function __construct(
        public AnPlusB $anPlusB,
        public ?SelectorList $selectorList = null
    ) {
        $args = [$this->anPlusB];
        if ($this->selectorList) {
            $args[] = $this->selectorList;
        }

        parent::__construct('nth-child', $args);
    }

    public function __toString()
    {
        return sprintf(
            ':nth-child(%s%s)',
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
}
