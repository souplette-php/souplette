<?php declare(strict_types=1);

namespace JoliPotage\Css\Selectors\Node;

final class RelativeSelector extends Selector
{
    private string $combinator;
    private ComplexSelector $selector;

    public function __construct(string $combinator, ComplexSelector $selector)
    {
        $this->combinator = $combinator;
        $this->selector = $selector;
    }

    public function __toString()
    {
        return "{$this->combinator} {$this->selector}";
    }
}
