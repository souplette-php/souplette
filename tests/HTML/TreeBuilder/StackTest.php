<?php declare(strict_types=1);

namespace Souplette\Tests\HTML\TreeBuilder;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Souplette\HTML\TreeBuilder\Stack;

class StackTest extends TestCase
{
    public function testContains()
    {
        $stack = new Stack(['a', 'b', 'c']);
        Assert::assertTrue($stack->contains('c'));
        Assert::assertFalse($stack->contains('foo'));
    }

    public function testIndexOf()
    {
        $stack = new Stack(['a', 'b', 'c']);
        Assert::assertSame(0, $stack->indexOf('c'));
        Assert::assertSame(1, $stack->indexOf('b'));
        Assert::assertSame(2, $stack->indexOf('a'));
        Assert::assertNull($stack->indexOf('foo'));
    }

    /**
     * @param array $init
     * @param int $offset
     * @param string $expected
     */
    #[DataProvider('getProvider')]
    public function testGet(array $init, int $offset, string $expected)
    {
        $stack = new Stack($init);
        Assert::assertSame($expected, $stack->get($offset));
    }

    public static function getProvider(): iterable
    {
        $init = ['a', 'b', 'c', 'd'];
        // negative index yields in reverse order
        yield [$init, -1, 'a'];
        yield [$init, -2, 'b'];
        yield [$init, -3, 'c'];
        yield [$init, -4, 'd'];
        // positive index yields same as offsetGet
        yield [$init, 0, 'd'];
        yield [$init, 1, 'c'];
        yield [$init, 2, 'b'];
        yield [$init, 3, 'a'];
    }

    public function testClear()
    {
        $stack = new Stack(['a', 'b', 'c']);
        $stack->clear();
        Assert::assertTrue($stack->isEmpty());
    }

    /**
     * @param array $init
     * @param string $rm
     * @param array $expected
     */
    #[DataProvider('removeProvider')]
    public function testRemove(array $init, string $rm, array $expected)
    {
        $stack = new Stack($init);
        $stack->remove($rm);
        Assert::assertSame($expected, iterator_to_array($stack));
    }

    public static function removeProvider(): iterable
    {
        $init = ['a', 'b', 'c'];
        yield [$init, 'a', [1 => 'c', 0 => 'b']];
        yield [$init, 'b', [1 => 'c', 0 => 'a']];
        yield [$init, 'c', [1 => 'b', 0 => 'a']];
    }

    /**
     * @param array $init
     * @param array $replace
     * @param array $expected
     */
    #[DataProvider('replaceProvider')]
    public function testReplace(array $init, array $replace, array $expected)
    {
        $stack = new Stack($init);
        $stack->replace(...$replace);
        Assert::assertSame($expected, iterator_to_array($stack));
    }

    public static function replaceProvider(): iterable
    {
        $init = ['a', 'b', 'c'];
        $rep = 'ins';
        yield [$init, ['a', $rep], [2 => 'c', 1 => 'b', 0 => 'ins']];
        yield [$init, ['b', $rep], [2 => 'c', 1 => 'ins', 0 => 'a']];
        yield [$init, ['c', $rep], [2 => 'ins', 1 => 'b', 0 => 'a']];
    }

    public function testPopUntil()
    {
        $stack = new Stack(['a', 'b', 'c', 'd']);
        $result = $stack->popUntil('c');
        Assert::assertSame('c', $result);
        Assert::assertSame([1 => 'b', 0 => 'a'], iterator_to_array($stack));
    }

    public function testPopUntilWithEmptyStack()
    {
        $stack = new Stack();
        Assert::assertNull($stack->popUntil('foo'));
    }

    /**
     * @param array $init
     * @param array $insert
     * @param array $expected
     */
    #[DataProvider('insertProvider')]
    public function testInsert(array $init, array $insert, array $expected)
    {
        $stack = new Stack($init);
        $stack->insert(...$insert);
        Assert::assertSame($expected, iterator_to_array($stack));
    }

    public static function insertProvider(): iterable
    {
        $init = ['a', 'b', 'c', 'd'];
        $ins = 'ins';

        yield [$init, [0, $ins], [4 => 'ins', 3 => 'd', 2 => 'c', 1 => 'b', 0 => 'a']];
        yield [$init, [1, $ins], [4 => 'd', 3 => 'ins', 2 => 'c', 1 => 'b', 0 => 'a']];
        yield [$init, [2, $ins], [4 => 'd', 3 => 'c', 2 => 'ins', 1 => 'b', 0 => 'a']];
        yield [$init, [3, $ins], [4 => 'd', 3 => 'c', 2 => 'b', 1 => 'ins', 0 => 'a']];
        yield [$init, [4, $ins], [4 => 'd', 3 => 'c', 2 => 'b', 1 => 'a', 0 => 'ins']];
        yield [$init, [5, $ins], [4 => 'd', 3 => 'c', 2 => 'b', 1 => 'a', 0 => 'ins']];
        //
        yield [$init, [-1, $ins], [4 => 'd', 3 => 'c', 2 => 'b', 1 => 'a', 0 => 'ins']];
        yield [$init, [-2, $ins], [4 => 'd', 3 => 'c', 2 => 'b', 1 => 'ins', 0 => 'a']];
        yield [$init, [-3, $ins], [4 => 'd', 3 => 'c', 2 => 'ins', 1 => 'b', 0 => 'a']];
        yield [$init, [-4, $ins], [4 => 'd', 3 => 'ins', 2 => 'c', 1 => 'b', 0 => 'a']];
        yield [$init, [-5, $ins], [4 => 'ins', 3 => 'd', 2 => 'c', 1 => 'b', 0 => 'a']];
        yield [$init, [-6, $ins], [4 => 'ins', 3 => 'd', 2 => 'c', 1 => 'b', 0 => 'a']];
    }
}
