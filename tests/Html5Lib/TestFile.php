<?php declare(strict_types=1);

namespace Souplette\Tests\Html5Lib;

abstract class TestFile implements \ArrayAccess, \IteratorAggregate
{
    /**
     * @var string;
     */
    protected $fileName;
    /**
     * @var array
     */
    protected $tests;

    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
        $this->tests = $this->parse($fileName);
    }

    abstract protected function parse(string $fileName): array;

    public function getIterator()
    {
        return new \ArrayIterator($this->tests);
    }

    public function offsetExists($offset)
    {
        return isset($this->tests[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->tests[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->tests[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->tests[$offset]);
    }
}
