<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

use Souplette\Css\Selectors\Specificity;

class TypeSelector extends SimpleSelector
{
    public function __construct(public string $tagName, public ?string $namespace = null)
    {
    }

    public function __toString(): string
    {
        if ($this->namespace === '*' || !$this->namespace) {
            return $this->tagName;
        }
        return "{$this->namespace}|{$this->tagName}";
    }

    public function getSpecificity(): Specificity
    {
        return new Specificity(0, 0, 1);
    }
}
