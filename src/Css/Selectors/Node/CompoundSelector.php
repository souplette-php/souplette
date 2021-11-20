<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

use Souplette\Css\Selectors\Specificity;
use Traversable;

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

    public function simpleSelectors(): iterable
    {
        yield from $this->selectors;
    }

    /**
     * @return Traversable<Selector>
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->selectors);
    }

    public function count(): int
    {
        return \count($this->selectors);
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
