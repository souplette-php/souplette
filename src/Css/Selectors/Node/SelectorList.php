<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Selectors\Specificity;
use Souplette\Dom\Element;
use Traversable;

final class SelectorList extends Selector implements \IteratorAggregate, \Countable
{
    public function __construct(
        /** @var Selector[] */
        public array $selectors,
    ) {
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

    public function matches(QueryContext $context, Element $element): bool
    {
        foreach ($this->selectors as $selector) {
            if ($selector->matches($context, $element)) {
                return true;
            }
        }
        return false;
    }
}
