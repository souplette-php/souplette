<?php declare(strict_types=1);

namespace Souplette\Css\Selectors;

use JetBrains\PhpStorm\Pure;

/**
 * @see https://www.w3.org/TR/selectors-4/#specificity-rules
 */
final class Specificity
{
    public function __construct(
        public readonly int $a = 0,
        public readonly int $b = 0,
        public readonly int $c = 0,
    ) {
    }

    #[Pure]
    public function add(self $specificity): self
    {
        return new self(
            $this->a + $specificity->a,
            $this->b + $specificity->b,
            $this->c + $specificity->c,
        );
    }

    #[Pure]
    public function isGreaterThan(self $specificity): bool
    {
        return $this->compare($specificity) > 0;
    }

    public function compare(self $specificity): int
    {
        if ($result = $this->a <=> $specificity->a) {
            return $result;
        }
        if ($result = $this->b <=> $specificity->b) {
            return $result;
        }
        return $this->c <=> $specificity->c;
    }

    public function __toString(): string
    {
        return sprintf('(%d,%d,%d)', $this->a, $this->b, $this->c);
    }
}
