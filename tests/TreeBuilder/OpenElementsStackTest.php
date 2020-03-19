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

    public function testContainsTag()
    {
        $stack = $this->createStack('a', 'b', 'c');
        Assert::assertTrue($stack->containsTag('b'));
        Assert::assertFalse($stack->containsTag('foo'));
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
