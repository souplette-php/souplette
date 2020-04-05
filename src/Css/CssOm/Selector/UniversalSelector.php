<?php declare(strict_types=1);

namespace JoliPotage\Css\CssOm\Selector;

final class UniversalSelector extends TypeSelector
{
    public function __construct()
    {
        parent::__construct('*', '*');
    }
}
