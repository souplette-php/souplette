<?php declare(strict_types=1);

namespace JoliPotage\Css\Selectors\Node\Functional;

use JoliPotage\Css\Selectors\Node\FunctionalSelector;
use JoliPotage\Css\Syntax\Node\AnPlusB;

final class NthOfType extends FunctionalSelector
{
    public function __construct(AnPlusB $anPlusB)
    {
        parent::__construct('nth-of-type', [$anPlusB]);
    }

    public function __toString()
    {
        return ":nth-of-type({$this->arguments[0]})";
    }
}