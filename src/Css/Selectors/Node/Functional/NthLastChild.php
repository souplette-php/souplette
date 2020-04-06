<?php declare(strict_types=1);

namespace JoliPotage\Css\Selectors\Node\Functional;

use JoliPotage\Css\Selectors\Node\FunctionalSelector;
use JoliPotage\Css\Syntax\Node\AnPlusB;

final class NthLastChild extends FunctionalSelector
{
    public function __construct(AnPlusB $anPlusB, array $selectors = [])
    {
        parent::__construct('nth-last-child', [$anPlusB, $selectors]);
    }

    public function __toString()
    {
        $args = (string)$this->arguments[0];
        $selectors = $this->arguments[1];
        if ($selectors) {
            $args .= ' of ';
            $args .= implode(', ', array_map(fn($s) => (string)$s, $selectors));
        }
        return ":nth-last-child({$args})";
    }
}
