<?php declare(strict_types=1);

namespace JoliPotage\Css\Selectors\Node\Functional;

use JoliPotage\Css\Selectors\Node\FunctionalSelector;
use JoliPotage\Css\Syntax\Node\AnPlusB;

final class NthCol extends FunctionalSelector
{
    public function __construct(AnPlusB $anPlusB)
    {
        parent::__construct('nth-col', [$anPlusB]);
    }

    public function __toString()
    {
        return ":nth-col({$this->arguments[0]})";
    }
}
