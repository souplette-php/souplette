<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

final class SelectorList extends Selector implements \IteratorAggregate, \Countable
{
    /**
     * @var Selector[]
     */
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
