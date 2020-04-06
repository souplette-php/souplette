<?php declare(strict_types=1);

namespace JoliPotage\Css\Selectors\Node\Functional;

use JoliPotage\Css\Selectors\Node\FunctionalSelector;
use JoliPotage\Css\Syntax\Node\AnPlusB;

final class NthLastCol extends FunctionalSelector
{
    public function __construct(AnPlusB $anPlusB)
    {
        parent::__construct('nth-last-col', [$anPlusB]);
    }

    public function __toString()
    {
        return ":nth-last-col({$this->arguments[0]})";
    }
}
