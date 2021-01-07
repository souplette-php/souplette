<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

class PseudoClassSelector extends SimpleSelector
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return ":{$this->name}";
    }
}
