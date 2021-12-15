<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node\Simple;

use Souplette\CSS\Selectors\Node\SimpleSelector;
use Souplette\CSS\Selectors\Specificity;

final class PseudoElementSelector extends SimpleSelector
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return "::{$this->name}";
    }

    public function getSpecificity(): Specificity
    {
        return new Specificity(0, 0, 1);
    }
}
