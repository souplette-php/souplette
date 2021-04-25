<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

use Souplette\Css\Selectors\Specificity;

final class RelativeSelector extends Selector
{
    public function __construct(
        public string $combinator,
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
            Combinators::DESCENDANT => " {$this->selector}",
            default => "{$this->combinator} {$this->selector}",
        };
    }

    public function getSpecificity(): Specificity
    {
        return $this->selector->getSpecificity();
    }
}
