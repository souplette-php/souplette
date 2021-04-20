<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;

class FunctionalSelector extends PseudoClassSelector
{
    public function __construct(
        string $name,
        protected array $arguments = [])
    {
        parent::__construct($name);
    }

    public function __toString(): string
    {
        $args = array_map(fn($arg) => (string)$arg, $this->arguments);
        return sprintf(':%s(%s)', $this->name, implode('', $args));
    }
}
