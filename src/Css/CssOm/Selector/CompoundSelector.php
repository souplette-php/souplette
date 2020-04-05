<?php declare(strict_types=1);

namespace JoliPotage\Css\CssOm\Selector;

final class CompoundSelector extends Selector
{
    public array $selectors;

    public function __construct(array $selectors)
    {
        $this->selectors = $selectors;
    }
}
