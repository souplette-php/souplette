<?php declare(strict_types=1);

namespace Souplette\Dom\Collections;

use Souplette\Dom\Internal\Idioms;

/**
 * This class backs up the implementation of DOMTokenList interface.
 *
 * @see https://dom.spec.whatwg.org/#ordered-sets
 * @see https://dom.spec.whatwg.org/#interface-domtokenlist
 *
 * @internal
 */
final class OrderedTokenSet implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * @var array<int, string>
     */
    private array $tokens = [];
    /**
     * @var array<string, int>
     */
    private array $indices = [];

    public function parse(string $value): void
    {
        $this->tokens = [];
        $this->indices = [];
        foreach (Idioms::splitInputOnAsciiWhitespace($value) as $i => $token) {
            $this->tokens[] = $token;
            $this->indices[$token] = $i;
        }
    }

    public function serialize(): string
    {
        return implode(' ', $this->tokens);
    }

    /**
     * @return string[]
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function isEmpty(): bool
    {
        return \count($this->tokens) === 0;
    }

    public function contains(string $token): bool
    {
        return isset($this->indices[$token]);
    }

    public function add(string $token): bool
    {
        if ($this->contains($token)) {
            return false;
        }
        $index = \array_key_last($this->tokens) ?? -1;
        $index++;
        $this->tokens[$index] = $token;
        $this->indices[$token] = $index;
        return true;
    }

    public function remove(string $token): bool
    {
        if (!$this->contains($token)) {
            return false;
        }
        $index = $this->indices[$token];
        unset($this->indices[$token], $this->tokens[$index]);
        return true;
    }

    public function replace(string $old, string $new): bool
    {
        if (!$this->contains($old)) {
            return false;
        }
        $index = $this->indices[$old];
        unset($this->indices[$old]);
        $this->indices[$new] = $index;
        $this->tokens[$index] = $new;
        return true;
    }

    public function count(): int
    {
        return \count($this->tokens);
    }

    public function getIterator(): \Traversable
    {
        yield from array_values($this->tokens);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->tokens[$offset]);
    }

    public function offsetGet($offset): ?string
    {
        return $this->tokens[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        // does nothing...
    }

    public function offsetUnset($offset): void
    {
        // does nothing...
    }
}
