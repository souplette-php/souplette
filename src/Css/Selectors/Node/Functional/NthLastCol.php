<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Functional;

use Souplette\Css\Selectors\Node\FunctionalSelector;
use Souplette\Css\Syntax\Node\AnPlusB;

final class NthLastCol extends FunctionalSelector
{
    public function __construct(AnPlusB $anPlusB)
    {
        parent::__construct('nth-last-col', [$anPlusB]);
    }

    public function __toString(): string
    {
        return ":nth-last-col({$this->arguments[0]})";
    }
}
