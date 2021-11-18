<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

use Souplette\Css\Selectors\Specificity;

final class ComplexSelector extends Selector
{
    public function __construct(
        public Selector $lhs,
        public ?Combinator $combinator = null,
        public ?Selector $rhs = null
    ) {
    }

    public function simpleSelectors(): \Generator
    {
        yield from $this->lhs;
        yield from $this->rhs;
    }

    public function __toString(): string
    {
        $combinator = match ($this->combinator) {
            Combinator::DESCENDANT => ' ',
            default => " {$this->combinator->value} ",
        };
        return "{$this->lhs}{$combinator}{$this->rhs}";
    }

    public function getSpecificity(): Specificity
    {
        return $this->lhs->getSpecificity()->add($this->rhs->getSpecificity());
    }
}
