<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

use Souplette\Css\Selectors\Specificity;

final class PseudoElementSelector extends SimpleSelector
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return "::{$this->name}";
    }

    public function getSpecificity(): Specificity
    {
        return new Specificity(0, 0, 1);
    }
}
