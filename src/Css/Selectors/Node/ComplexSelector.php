<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

final class ComplexSelector extends Selector
{
    private Selector $lhs;
    private ?Selector $rhs;
    private ?string $combinator;

    public function __construct(Selector $lhs, ?string $combinator = null, ?Selector $rhs = null)
    {
        $this->lhs = $lhs;
        $this->rhs = $rhs;
        $this->combinator = $combinator;
    }

    public function __toString()
    {
        $combinator = $this->combinator === Combinators::DESCENDANT ? ' ' : " {$this->combinator} ";
        return "{$this->lhs}{$combinator}{$this->rhs}";
    }
}
