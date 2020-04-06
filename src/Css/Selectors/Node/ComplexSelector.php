<?php declare(strict_types=1);

namespace JoliPotage\Css\Selectors\Node;

final class ComplexSelector extends Selector implements \IteratorAggregate, \Countable
{
    /**
     * @var CompoundSelector[]
     */
    private array $selectors;

    public function __construct()
    {
        $this->selectors = [];
    }

    public function add(?string $combinator, CompoundSelector $selector): void
    {
        $this->selectors[] = [$combinator, $selector];
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
        foreach ($this->selectors as [$combinator, $selector]) {
            $s .= "{$combinator}{$selector}";
        }

        return $s;
    }
}
