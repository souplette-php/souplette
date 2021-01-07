<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

final class CompoundSelector extends Selector implements \IteratorAggregate, \Countable
{
    public array $selectors;

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
        $s = '';
        foreach ($this->selectors as $selector) {
            $s .= "{$selector}";
        }
        return $s;
    }
}
