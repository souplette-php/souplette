<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

class TypeSelector extends SimpleSelector
{
    protected string $tagName;
    protected ?string $namespace;

    public function __construct(string $tagName, ?string $namespace = null)
    {
        $this->tagName = $tagName;
        $this->namespace = $namespace;
    }

    public function __toString()
    {
        if ($this->namespace === '*' || !$this->namespace) {
            return $this->tagName;
        }
        return "{$this->namespace}|{$this->tagName}";
    }
}
