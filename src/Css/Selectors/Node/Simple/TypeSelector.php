<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Simple;

use Souplette\Css\Selectors\Namespaces;
use Souplette\Css\Selectors\Node\SimpleSelector;
use Souplette\Css\Selectors\Specificity;

class TypeSelector extends SimpleSelector
{
    public function __construct(
        public string $tagName,
        public ?string $namespace = null
    ) {
    }

    public function __toString(): string
    {
        // @see https://drafts.csswg.org/selectors/#type-nmsp
        return match($this->namespace) {
            Namespaces::NONE => "|{$this->tagName}",
            Namespaces::ANY => $this->tagName,
            // TODO: default namespace must be resolved
            Namespaces::DEFAULT => "*|{$this->tagName}",
            default => "{$this->namespace}|{$this->tagName}"
        };
    }

    public function getSpecificity(): Specificity
    {
        return new Specificity(0, 0, 1);
    }
}
