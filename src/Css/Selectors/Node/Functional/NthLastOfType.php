<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Functional;

use Souplette\Css\Selectors\Node\FunctionalSelector;
use Souplette\Css\Syntax\Node\AnPlusB;

final class NthLastOfType extends FunctionalSelector
{
    public function __construct(
        public AnPlusB $anPlusB
    ) {
        parent::__construct('nth-last-of-type', [$anPlusB]);
    }

    public function __toString()
    {
        return ":nth-last-of-type({$this->anPlusB})";
    }
}
