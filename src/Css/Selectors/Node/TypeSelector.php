<?php declare(strict_types=1);

namespace JoliPotage\Css\Selectors\Node;

class TypeSelector extends SimpleSelector
{
    protected string $namespace;
    protected string $tagName;

    public function __construct(string $namespace, string $tagName)
    {
        $this->namespace = $namespace;
        $this->tagName = $tagName;
    }

    public function __toString()
    {
        if ($this->namespace === '*') {
            return $this->tagName;
        }
        return "{$this->namespace}|{$this->tagName}";
    }
}
