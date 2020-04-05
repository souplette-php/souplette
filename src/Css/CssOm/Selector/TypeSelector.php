<?php declare(strict_types=1);

namespace JoliPotage\Css\CssOm\Selector;

class TypeSelector extends Selector
{
    protected string $namespace;
    protected string $tagName;

    public function __construct(string $namespace, string $tagName)
    {
        $this->namespace = $namespace;
        $this->tagName = $tagName;
    }
}
