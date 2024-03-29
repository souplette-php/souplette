<?php declare(strict_types=1);

namespace Souplette\Tests\HTML5Lib;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Traversable;

abstract class TestFile implements ArrayAccess, IteratorAggregate
{
    protected string $fileName;
    protected array $tests;

    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
        $this->tests = $this->parse($fileName);
    }

    abstract protected function parse(string $fileName): array;

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->tests);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->tests[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->tests[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->tests[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->tests[$offset]);
    }
}
