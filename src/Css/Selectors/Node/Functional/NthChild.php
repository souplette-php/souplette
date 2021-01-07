<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Functional;

use Souplette\Css\Selectors\Node\FunctionalSelector;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Syntax\Node\AnPlusB;

final class NthChild extends FunctionalSelector
{
    public function __construct(AnPlusB $anPlusB, ?SelectorList $selectors = null)
    {
        parent::__construct('nth-child', [$anPlusB, $selectors]);
    }

    public function __toString()
    {
        $args = (string)$this->arguments[0];
        $selectors = $this->arguments[1];
        if ($selectors) {
            $args .= " of {$selectors}";
        }
        return ":nth-child({$args})";
    }
}
