<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

use Souplette\Css\Selectors\Specificity;

final class RelativeSelector extends Selector
{
    private string $combinator;
    private ComplexSelector $selector;

    public function __construct(string $combinator, ComplexSelector $selector)
    {
        $this->combinator = $combinator;
        $this->selector = $selector;
    }

    public function __toString(): string
    {
        return "{$this->combinator} {$this->selector}";
    }

    public function getSpecificity(): Specificity
    {
        return $this->selector->getSpecificity();
    }
}
