<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

use Souplette\Css\Selectors\Specificity;

final class SelectorList extends Selector implements \IteratorAggregate, \Countable
{
    /**
     * @var Selector[]
     */
    public array $selectors;

    public function __construct(array $selectors)
    {
        $this->selectors = $selectors;
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
        return implode(', ', array_map(fn($s) => (string)$s, $this->selectors));
    }

    public function getSpecificity(): Specificity
    {
        // If the selector is a selector list, this number is calculated for each selector in the list.
        // For a given matching process against the list, the specificity in effect is that of the most specific selector in the list that matches.
        $max = new Specificity();
        foreach ($this->selectors as $selector) {
            $spec = $selector->getSpecificity();
            if ($spec->isGreaterThan($max)) {
                $max = $spec;
            }
        }

        return $max;
    }
}
