<?php declare(strict_types=1);

namespace JoliPotage\Css\Selectors\Node\Functional;

use JoliPotage\Css\Selectors\Node\FunctionalSelector;
use JoliPotage\Css\Selectors\Node\SelectorList;

final class Has extends FunctionalSelector
{
    public function __construct(SelectorList $selectors)
    {
        parent::__construct('has', [$selectors]);
    }

    public function __toString()
    {
        return ":has({$this->arguments[0]})";
    }
}
