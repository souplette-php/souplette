<?php declare(strict_types=1);

namespace JoliPotage\Css\CssOm\Selector;

final class ComplexSelector extends Selector
{
    private Selector $lhs;
    private string $combinator;
    private Selector $rhs;

    public function __construct(Selector $lhs, string $combinator, Selector $rhs)
    {
        $this->lhs = $lhs;
        $this->combinator = $combinator;
        $this->rhs = $rhs;
    }
}
