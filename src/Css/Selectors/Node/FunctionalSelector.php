<?php declare(strict_types=1);

namespace JoliPotage\Css\Selectors\Node;

class FunctionalSelector extends PseudoClassSelector
{
    protected string $name;
    protected array $arguments = [];

    public function __construct(string $name, array $arguments = [])
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }

    public function __toString()
    {
        $args = array_map(fn($arg) => (string)$arg, $this->arguments);
        return ":{$this->name}({$args})";
    }
}
