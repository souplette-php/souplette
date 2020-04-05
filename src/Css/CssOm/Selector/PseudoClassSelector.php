<?php declare(strict_types=1);

namespace JoliPotage\Css\CssOm\Selector;

final class PseudoClassSelector extends Selector
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
