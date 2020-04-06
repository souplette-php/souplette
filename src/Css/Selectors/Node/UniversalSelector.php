<?php declare(strict_types=1);

namespace JoliPotage\Css\Selectors\Node;

final class UniversalSelector extends TypeSelector
{
    public function __construct()
    {
        parent::__construct('*', '*');
    }
}
