<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

class FunctionalSelector extends PseudoClassSelector
{
    protected string $name;
    protected array $arguments = [];

    public function __construct(string $name, array $arguments = [])
    {
        parent::__construct($name);
        $this->arguments = $arguments;
    }

    public function __toString()
    {
        $args = array_map(fn($arg) => (string)$arg, $this->arguments);
        return ":{$this->name}({$args})";
    }
}
