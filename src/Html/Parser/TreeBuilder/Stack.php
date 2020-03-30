<?php declare(strict_types=1);

namespace JoliPotage\Html\Parser\TreeBuilder;

class Stack extends \SplStack
{
    const IT_MODE_LIST = self::IT_MODE_FIFO | self::IT_MODE_KEEP;
    const IT_MODE_STACK = self::IT_MODE_LIFO | self::IT_MODE_KEEP;

    public function __construct(iterable $values = [])
    {
        foreach ($values as $value) {
            $this->push($value);
        }
    }

    public function contains($value): bool
    {
        foreach ($this as $entry) {
            if ($entry === $value) {
                return true;
            }
        }
        return false;
    }

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

    public function get(int $offset)
    {
        if ($offset >= 0) {
            return $this->offsetGet($offset);
        }
        return $this->offsetGet($this->count() + $offset);
    }

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

    public function clear()
    {
        while (!$this->isEmpty()) {
            $this->pop();
        }
    }

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
