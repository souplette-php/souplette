<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

use Souplette\Css\Selectors\Specificity;

class FunctionalSelector extends SimpleSelector
{
    public function __construct(
        public string $name,
        public array $arguments = [],
    ) {
    }

    public function __toString(): string
    {
        $args = array_map(fn($arg) => (string)$arg, $this->arguments);
        return sprintf(':%s(%s)', $this->name, implode('', $args));
    }

    public function getSpecificity(): Specificity
    {
        return new Specificity(0, 1);
    }
}
