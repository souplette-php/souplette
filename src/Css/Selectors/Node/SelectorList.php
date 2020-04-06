<?php declare(strict_types=1);

namespace JoliPotage\Css\Selectors\Node;

use JoliPotage\Css\Syntax\SyntaxNode;

final class SelectorList extends SyntaxNode implements \IteratorAggregate, \Countable
{
    private array $selectors;

    public function __construct(array $selectors)
    {
        $this->selectors = $selectors;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->selectors);
    }

    public function count()
    {
        return count($this->selectors);
    }

    public function __toString()
    {
        return implode(', ', array_map(fn($s) => (string)$s, $this->selectors));
    }
}
