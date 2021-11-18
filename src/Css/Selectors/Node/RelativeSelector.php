<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

use Souplette\Css\Selectors\Specificity;

final class RelativeSelector extends Selector
{
    public function __construct(
        public Combinator $combinator,
        public ComplexSelector $selector,
    ) {
    }

    public function simpleSelectors(): \Generator
    {
        yield from $this->selector;
    }

    public function __toString(): string
    {
        return match ($this->combinator) {
            Combinator::DESCENDANT => " {$this->selector}",
            default => "{$this->combinator->value} {$this->selector}",
        };
    }

    public function getSpecificity(): Specificity
    {
        return $this->selector->getSpecificity();
    }
}
