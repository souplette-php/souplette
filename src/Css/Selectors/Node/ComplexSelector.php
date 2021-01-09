<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

use Souplette\Css\Selectors\Specificity;

final class ComplexSelector extends Selector
{
    public function __construct(
        public Selector $lhs,
        public ?string $combinator = null,
        public ?Selector $rhs = null
    ) {
    }

    public function __toString(): string
    {
        $combinator = $this->combinator === Combinators::DESCENDANT ? ' ' : " {$this->combinator} ";
        return "{$this->lhs}{$combinator}{$this->rhs}";
    }

    public function getSpecificity(): Specificity
    {
        return $this->lhs->getSpecificity()->add($this->rhs->getSpecificity());
    }
}
