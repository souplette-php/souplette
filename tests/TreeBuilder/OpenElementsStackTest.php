<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\TreeBuilder;

use ju1ius\HtmlParser\Namespaces;
use ju1ius\HtmlParser\TreeBuilder\OpenElementsStack;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class OpenElementsStackTest extends TestCase
{
    private function createStack(string ...$tagNames): OpenElementsStack
    {
        $stack = new OpenElementsStack();
        foreach ($tagNames as $tagName) {
            $el = new \DOMElement($tagName,'', Namespaces::HTML);
            $stack->push($el);
        }

        return $stack;
    }

    public function testIndexOf()
    {
        $stack = new OpenElementsStack();
        $stack->push($a = new \DOMElement('a'));
        $stack->push($b = new \DOMElement('b'));
        $stack->push($c = new \DOMElement('c'));
        Assert::assertSame(0, $stack->indexOf($a));
        Assert::assertSame(1, $stack->indexOf($b));
        Assert::assertSame(2, $stack->indexOf($c));
        Assert::assertSame(-1, $stack->indexOf(new \DOMElement('foo')));
    }

    public function testClear()
    {
        $stack = $this->createStack('a', 'b', 'c');
        $stack->clear();
        Assert::assertTrue($stack->isEmpty());
    }

    public function testRemove()
    {
        $stack = new OpenElementsStack();
        $stack->push($a = new \DOMElement('a'));
        $stack->push($b = new \DOMElement('b'));
        $stack->push($c = new \DOMElement('c'));
        $stack->remove($b);
        Assert::assertSame([1 => $c, 0 => $a], iterator_to_array($stack));
    }

    /**
     * @dataProvider insertProvider
     * @param array $init
     * @param array $insert
     * @param array $expected
     */
    public function testInsert(array $init, array $insert, array $expected)
    {
        $stack = new OpenElementsStack();
        foreach ($init as $item) {
            $stack->push($item);
        }
        $stack->insert(...$insert);
        $result = [];
        foreach ($stack as $i => $node) {
            $result[$i] = $node->localName;
        }
        Assert::assertSame($expected, $result);
    }

    public function insertProvider()
    {
        $a = new \DOMElement('a');
        $b = new \DOMElement('b');
        $c = new \DOMElement('c');
        $d = new \DOMElement('d');
        $init = [$a, $b, $c, $d];
        $ins = new \DOMElement('ins');

        yield [$init, [0, $ins], [4 => 'd', 3 => 'c', 2 => 'b', 1 => 'a', 0 => 'ins']];
        yield [$init, [1, $ins], [4 => 'd', 3 => 'c', 2 => 'b', 1 => 'ins', 0 => 'a']];
        yield [$init, [2, $ins], [4 => 'd', 3 => 'c', 2 => 'ins', 1 => 'b', 0 => 'a']];
        yield [$init, [3, $ins], [4 => 'd', 3 => 'ins', 2 => 'c', 1 => 'b', 0 => 'a']];
        yield [$init, [4, $ins], [4 => 'ins', 3 => 'd', 2 => 'c', 1 => 'b', 0 => 'a']];
        yield [$init, [5, $ins], [4 => 'ins', 3 => 'd', 2 => 'c', 1 => 'b', 0 => 'a']];

        yield [$init, [-1, $ins], [4 => 'ins', 3 => 'd', 2 => 'c', 1 => 'b', 0 => 'a']];
        yield [$init, [-2, $ins], [4 => 'd', 3 => 'ins', 2 => 'c', 1 => 'b', 0 => 'a']];
        yield [$init, [-3, $ins], [4 => 'd', 3 => 'c', 2 => 'ins', 1 => 'b', 0 => 'a']];
        yield [$init, [-4, $ins], [4 => 'd', 3 => 'c', 2 => 'b', 1 => 'ins', 0 => 'a']];
        yield [$init, [-5, $ins], [4 => 'd', 3 => 'c', 2 => 'b', 1 => 'a', 0 => 'ins']];
        yield [$init, [-6, $ins], [4 => 'd', 3 => 'c', 2 => 'b', 1 => 'a', 0 => 'ins']];
    }

    public function testReplace()
    {
        $stack = new OpenElementsStack();
        $stack->push($a = new \DOMElement('a'));
        $stack->push($b = new \DOMElement('b'));
        $stack->push($c = new \DOMElement('c'));
        $stack->replace($b, $d = new \DOMElement('d'));
        Assert::assertSame([2 => $c, 1 => $d, 0 => $a], iterator_to_array($stack));
    }

    public function testContains()
    {
        $stack = new OpenElementsStack();
        $stack->push($a = new \DOMElement('a'));
        $stack->push($b = new \DOMElement('b'));
        $stack->push($c = new \DOMElement('c'));
        Assert::assertTrue($stack->contains($b));
        Assert::assertFalse($stack->contains(new \DOMElement('d')));
    }

    public function testContainsTag()
    {
        $stack = $this->createStack('a', 'b', 'c');
        Assert::assertTrue($stack->containsTag('b'));
        Assert::assertFalse($stack->containsTag('foo'));
    }

    public function testPopUntil()
    {
        $stack = new OpenElementsStack();
        $stack->push($a = new \DOMElement('a'));
        $stack->push($b = new \DOMElement('b'));
        $stack->push($c = new \DOMElement('c'));
        $stack->push($d = new \DOMElement('d'));
        $stack->popUntil($c);
        Assert::assertSame([1 => $b, 0 => $a], iterator_to_array($stack));
    }

    public function testPopUntilTag()
    {
        $stack = new OpenElementsStack();
        $stack->push($a = new \DOMElement('a', '', Namespaces::HTML));
        $stack->push($b = new \DOMElement('b', '', Namespaces::HTML));
        $stack->push($c = new \DOMElement('c', '', Namespaces::HTML));
        $stack->push($d = new \DOMElement('d', '', Namespaces::HTML));
        $stack->popUntilTag('c');
        Assert::assertSame([1 => $b, 0 => $a], iterator_to_array($stack));
    }

    public function testPopUntilOneOf()
    {
        $stack = new OpenElementsStack();
        $stack->push($a = new \DOMElement('a', '', Namespaces::HTML));
        $stack->push($b = new \DOMElement('b', '', Namespaces::HTML));
        $stack->push($c = new \DOMElement('c', '', Namespaces::HTML));
        $stack->push($d = new \DOMElement('d', '', Namespaces::HTML));
        $stack->popUntilOneOf(['a', 'b', 'c']);
        Assert::assertSame([1 => $b, 0 => $a], iterator_to_array($stack));
    }
}
