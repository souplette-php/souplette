<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Simple;

use Souplette\Css\Selectors\Node\SimpleSelector;
use Souplette\Css\Selectors\Specificity;

class TypeSelector extends SimpleSelector
{
    public function __construct(public string $tagName, public ?string $namespace = null)
    {
    }

    public function __toString(): string
    {
        return match($this->namespace) {
            '*', null => $this->tagName,
            default => "{$this->namespace}|{$this->tagName}"
        };
    }

    public function getSpecificity(): Specificity
    {
        return new Specificity(0, 0, 1);
    }
}
