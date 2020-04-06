<?php declare(strict_types=1);

namespace JoliPotage\Css\Selectors\Node\Functional;

use JoliPotage\Css\Selectors\Node\FunctionalSelector;
use JoliPotage\Css\Selectors\Node\SelectorList;

final class Is extends FunctionalSelector
{
    public function __construct(SelectorList $selectors)
    {
        parent::__construct('is', [$selectors]);
    }

    public function __toString()
    {
        return ":is({$this->arguments[0]})";
    }
}
