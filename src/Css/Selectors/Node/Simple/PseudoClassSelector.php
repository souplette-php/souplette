<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Simple;

use Souplette\Css\Selectors\Node\SimpleSelector;
use Souplette\Css\Selectors\Specificity;

class PseudoClassSelector extends SimpleSelector
{
    public function __construct(public string $name)
    {
    }

    public function __toString(): string
    {
        return ":{$this->name}";
    }

    public function getSpecificity(): Specificity
    {
        return new Specificity(0, 1);
    }
}
