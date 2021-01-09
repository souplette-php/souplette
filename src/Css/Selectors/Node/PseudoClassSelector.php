<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

use Souplette\Css\Selectors\Specificity;

class PseudoClassSelector extends SimpleSelector
{
    protected string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return ":{$this->name}";
    }

    public function getSpecificity(): Specificity
    {
        return new Specificity(0, 1);
    }
}
