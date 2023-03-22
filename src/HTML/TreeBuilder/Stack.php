<?php declare(strict_types=1);

namespace Souplette\HTML\TreeBuilder;

/**
 * @template T
 */
class Stack extends \SplStack
{
    /**
     * @param iterable<T> $values
     */
    public function __construct(iterable $values = [])
    {
        foreach ($values as $value) {
            $this->push($value);
        }
    }

    /**
     * @param T $value
     */
    public function contains($value): bool
    {
        foreach ($this as $entry) {
            if ($entry === $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param T $value
     */
    public function indexOf($value): ?int
    {
        $i = 0;
        foreach ($this as $entry) {
            if ($entry === $value) {
                return $i;
            }
            $i++;
        }

        return null;
    }

    /**
     * @return T
     */
    public function get(int $offset)
    {
        if ($offset >= 0) {
            return $this->offsetGet($offset);
        }
        return $this->offsetGet($this->count() + $offset);
    }

    /**
     * @param int $offset
     * @param T $value
     */
    public function insert(int $offset, $value): void
    {
        $lastIndex = $this->count() - 1;
        if ($offset === 0 || $offset < -$lastIndex - 1) {
            $this->push($value);
            return;
        }
        if ($offset === -1 || $offset > $lastIndex) {
            $this->unshift($value);
            return;
        }
        if ($offset < -1) {
            $this->add($lastIndex + $offset + 1, $value);
            return;
        }
        $this->add($offset - 1, $value);
    }

    /**
     * @param T $value
     */
    public function remove($value): bool
    {
        $i = 0;
        foreach ($this as $entry) {
            if ($entry === $value) {
                $this->offsetUnset($i);
                return true;
            }
            $i++;
        }
        return false;
    }

    /**
     * @param T $old
     * @param T $new
     */
    public function replace($old, $new): bool
    {
        $i = 0;
        foreach ($this as $entry) {
            if ($entry === $old) {
                $this->offsetSet($i, $new);
                return true;
            }
            $i++;
        }
        return false;
    }

    public function clear(): void
    {
        while (!$this->isEmpty()) {
            $this->pop();
        }
    }

    /**
     * @param T $value
     * @return T|null
     */
    public function popUntil($value)
    {
        while (!$this->isEmpty()) {
            $entry = $this->pop();
            if ($entry === $value) {
                return $entry;
            }
        }

        return null;
    }
}
