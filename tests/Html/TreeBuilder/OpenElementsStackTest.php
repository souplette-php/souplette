<?php declare(strict_types=1);

namespace Souplette\Tests\Html\TreeBuilder;

use Souplette\Dom\Element;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\Namespaces;
use Souplette\Html\TreeBuilder\OpenElementsStack;

class OpenElementsStackTest extends TestCase
{
    private function createStack(string ...$tagNames): OpenElementsStack
    {
        $stack = new OpenElementsStack();
        foreach ($tagNames as $tagName) {
            $el = new Element($tagName,Namespaces::HTML, null);
            $stack->push($el);
        }

        return $stack;
    }

    public function testContainsTag()
    {
        $stack = $this->createStack('a', 'b', 'c');
        Assert::assertTrue($stack->containsTag('b'));
        Assert::assertFalse($stack->containsTag('foo'));
    }

    public function testPopUntilTag()
    {
        $stack = new OpenElementsStack();
        $stack->push($a = new Element('a', Namespaces::HTML));
        $stack->push($b = new Element('b', Namespaces::HTML));
        $stack->push($c = new Element('c', Namespaces::HTML));
        $stack->push($d = new Element('d', Namespaces::HTML));
        $stack->popUntilTag('c');
        Assert::assertSame([1 => $b, 0 => $a], iterator_to_array($stack));
    }

    public function testPopUntilOneOf()
    {
        $stack = new OpenElementsStack();
        $stack->push($a = new Element('a', Namespaces::HTML));
        $stack->push($b = new Element('b', Namespaces::HTML));
        $stack->push($c = new Element('c', Namespaces::HTML));
        $stack->push($d = new Element('d', Namespaces::HTML));
        $stack->popUntilOneOf(['a', 'b', 'c']);
        Assert::assertSame([1 => $b, 0 => $a], iterator_to_array($stack));
    }
}
