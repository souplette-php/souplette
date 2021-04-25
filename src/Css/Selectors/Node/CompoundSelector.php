<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

use Souplette\Css\Selectors\Specificity;

final class CompoundSelector extends Selector implements \IteratorAggregate, \Countable
{
    /**
     * @var Selector[]
     */
    public array $selectors;

    public function __construct(array $selectors)
    {
        $this->selectors = $selectors;
    }

    public function simpleSelectors(): \Generator
    {
        yield from $this->selectors;
    }

    /**
     * @return Selector[]
     */
    public function getIterator(): iterable
    {
        return new \ArrayIterator($this->selectors);
    }

    public function count(): int
    {
        return count($this->selectors);
    }

    public function __toString(): string
    {
        return implode('', array_map(fn($s) => (string)$s, $this->selectors));
    }

    public function getSpecificity(): Specificity
    {
        $spec = new Specificity();
        foreach ($this->selectors as $selector) {
            $spec = $spec->add($selector->getSpecificity());
        }
        return $spec;
    }
}
