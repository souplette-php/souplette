<?php declare(strict_types=1);

namespace Souplette\Tests\DOM;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Souplette\DOM\Document;
use Souplette\DOM\Node;

final class ChildNodeTest extends TestCase
{
    #[DataProvider('removeProvider')]
    public function testRemove(Document $doc, Node $node)
    {
        $parent = $node->parentNode;
        $node->remove();
        Assert::assertCount(0, $parent->childNodes);
        Assert::assertNull($node->parentNode);
        Assert::assertNull($parent->firstChild);
        Assert::assertNull($parent->lastChild);
    }

    public static function removeProvider(): iterable
    {
        yield 'works on text nodes' => [
            $doc = DOMBuilder::html()->tag('html')->text('foo')->getDocument(),
            $doc->documentElement->firstChild,
        ];
        yield 'works on comment nodes' => [
            $doc = DOMBuilder::html()->tag('html')->comment('foo')->getDocument(),
            $doc->documentElement->firstChild,
        ];
        yield 'works on element nodes' => [
            $doc = DOMBuilder::html()->tag('foo')->getDocument(),
            $doc->documentElement,
        ];
    }

    #[DataProvider('beforeProvider')]
    public function testBefore(Node $target, array $nodes)
    {
        $target->before(...$nodes);
        $expected = $target->previousSibling;
        foreach (array_reverse($nodes) as $node) {
            if (\is_string($node)) {
                Assert::assertSame($expected->data, $node);
            } else {
                Assert::assertSame($expected, $node);
            }
            $expected = $expected->previousSibling;
        }
    }

    public static function beforeProvider(): iterable
    {
        $doc = DOMBuilder::html()->tag('html')->tag('test')->getDocument();
        yield 'works on elements' => [
            $doc->documentElement->firstChild,
            [$doc->createElement('foo'), 'bar', $doc->createComment('baz')],
        ];
        //
        $doc = DOMBuilder::html()->tag('html')->text('test')->getDocument();
        yield 'works on text nodes' => [
            $doc->documentElement->firstChild,
            [$doc->createElement('foo'), 'bar', $doc->createComment('baz')],
        ];
        //
        $doc = DOMBuilder::html()->tag('html')->comment('test')->getDocument();
        yield 'works on comment nodes' => [
            $doc->documentElement->firstChild,
            [$doc->createElement('foo'), 'bar', $doc->createComment('baz')],
        ];
        //
        $doc = DOMBuilder::html()->tag('html')
            ->text('foo')
            ->text('target')
            ->getDocument();
        yield 'works when previous sibling is in nodes' => [
            $doc->documentElement->lastChild,
            ['bar', $doc->documentElement->firstChild, 'baz']
        ];
    }

    #[DataProvider('afterProvider')]
    public function testAfter(Node $target, array $nodes)
    {
        $target->after(...$nodes);
        $expected = $target->nextSibling;
        foreach ($nodes as $node) {
            if (\is_string($node)) {
                Assert::assertSame($expected->data, $node);
            } else {
                Assert::assertSame($expected, $node);
            }
            $expected = $expected->nextSibling;
        }
    }

    public static function afterProvider(): iterable
    {
        $doc = DOMBuilder::html()->tag('html')->tag('test')->getDocument();
        yield 'works on elements' => [
            $doc->documentElement->firstChild,
            [$doc->createElement('foo'), 'bar', $doc->createComment('baz')],
        ];
        //
        $doc = DOMBuilder::html()->tag('html')->text('test')->getDocument();
        yield 'works on text nodes' => [
            $doc->documentElement->firstChild,
            [$doc->createElement('foo'), 'bar', $doc->createComment('baz')],
        ];
        //
        $doc = DOMBuilder::html()->tag('html')->comment('test')->getDocument();
        yield 'works on comment nodes' => [
            $doc->documentElement->firstChild,
            [$doc->createElement('foo'), 'bar', $doc->createComment('baz')],
        ];
        //
        $doc = DOMBuilder::html()->tag('html')
            ->text('target')
            ->text('foo')
            ->getDocument();
        yield 'works when next sibling is in nodes' => [
            $doc->documentElement->firstChild,
            ['bar', $doc->documentElement->lastChild, 'baz']
        ];
    }

    #[DataProvider('replaceWithProvider')]
    public function testReplaceWith(Node $target, array $nodes)
    {
        $parent = $target->parentNode;
        $target->replaceWith(...$nodes);
        Assert::assertCount(\count($nodes), $parent->childNodes);
        $expected = $parent->firstChild;
        foreach ($nodes as $node) {
            if (\is_string($node)) {
                Assert::assertSame($expected->data, $node);
            } else {
                Assert::assertSame($expected, $node);
            }
            $expected = $expected->nextSibling;
        }
    }

    public static function replaceWithProvider(): iterable
    {
        $doc = DOMBuilder::html()->tag('html')
            ->tag('test')
            ->getDocument();
        yield 'works on elements' => [
            $doc->documentElement->firstChild,
            ['foo', $doc->createElement('bar'), $doc->createComment('baz')],
        ];
        //
        $doc = DOMBuilder::html()->tag('html')
            ->text('test')
            ->getDocument();
        yield 'works on text nodes' => [
            $doc->documentElement->firstChild,
            ['foo', $doc->createElement('bar'), $doc->createComment('baz')],
        ];
        //
        $doc = DOMBuilder::html()->tag('html')
            ->comment('test')
            ->getDocument();
        yield 'works on comment nodes' => [
            $doc->documentElement->firstChild,
            ['foo', $doc->createElement('bar'), $doc->createComment('baz')],
        ];
        //
        $doc = DOMBuilder::html()->tag('html')
            ->tag('test')
            ->getDocument();
        yield 'works when target is in nodes' => [
            $doc->documentElement->firstChild,
            ['foo', $doc->documentElement->firstChild, $doc->createComment('baz')],
        ];
    }
}
