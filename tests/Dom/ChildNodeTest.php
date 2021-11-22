<?php declare(strict_types=1);

namespace Souplette\Tests\Dom;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\Api\ChildNodeInterface;
use Souplette\Dom\Node\HtmlDocument;

final class ChildNodeTest extends TestCase
{
    /**
     * @dataProvider removeProvider
     * @param HtmlDocument $doc
     * @param ChildNodeInterface $node
     */
    public function testRemove(HtmlDocument $doc, ChildNodeInterface $node)
    {
        $node->remove();
        Assert::assertCount(0, $doc->childNodes);
    }

    public function removeProvider(): iterable
    {
        yield 'works on text nodes' => [
            $doc = DomBuilder::create()->text('foo')->getDocument(),
            $doc->firstChild,
        ];
        yield 'works on comment nodes' => [
            $doc = DomBuilder::create()->comment('foo')->getDocument(),
            $doc->firstChild,
        ];
        yield 'works on element nodes' => [
            $doc = DomBuilder::create()->tag('foo')->getDocument(),
            $doc->firstChild,
        ];
    }

    /**
     * @dataProvider beforeProvider
     */
    public function testBefore(\DOMChildNode $target, array $nodes)
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

    public function beforeProvider(): iterable
    {
        $doc = DomBuilder::create()->tag('test')->getDocument();
        yield 'works on elements' => [
            $doc->firstChild,
            [$doc->createElement('foo'), 'bar', $doc->createComment('baz')],
        ];
        //
        $doc = DomBuilder::create()->text('test')->getDocument();
        yield 'works on text nodes' => [
            $doc->firstChild,
            [$doc->createElement('foo'), 'bar', $doc->createComment('baz')],
        ];
        //
        $doc = DomBuilder::create()->comment('test')->getDocument();
        yield 'works on comment nodes' => [
            $doc->firstChild,
            [$doc->createElement('foo'), 'bar', $doc->createComment('baz')],
        ];
        //
        $doc = DomBuilder::create()
            ->text('foo')
            ->text('target')
            ->getDocument();
        yield 'works when previous sibling is in nodes' => [
            $doc->lastChild,
            ['bar', $doc->firstChild, 'baz']
        ];
    }

    /**
     * @dataProvider afterProvider
     * @param Node $target
     * @param $nodes
     */
    public function testAfter($target, array $nodes)
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

    public function afterProvider(): iterable
    {
        $doc = DomBuilder::create()->tag('test')->getDocument();
        yield 'works on elements' => [
            $doc->firstChild,
            [$doc->createElement('foo'), 'bar', $doc->createComment('baz')],
        ];
        //
        $doc = DomBuilder::create()->text('test')->getDocument();
        yield 'works on text nodes' => [
            $doc->firstChild,
            [$doc->createElement('foo'), 'bar', $doc->createComment('baz')],
        ];
        //
        $doc = DomBuilder::create()->comment('test')->getDocument();
        yield 'works on comment nodes' => [
            $doc->firstChild,
            [$doc->createElement('foo'), 'bar', $doc->createComment('baz')],
        ];
        //
        $doc = DomBuilder::create()
            ->text('target')
            ->text('foo')
            ->getDocument();
        yield 'works when next sibling is in nodes' => [
            $doc->firstChild,
            ['bar', $doc->lastChild, 'baz']
        ];
    }

    /**
     * @dataProvider replaceWithProvider
     */
    public function testReplaceWith($target, array $nodes)
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

    public function replaceWithProvider(): iterable
    {
        $doc = DomBuilder::create()->tag('test')->getDocument();
        yield 'works on elements' => [
            $doc->firstChild,
            ['foo', $doc->createElement('bar'), $doc->createComment('baz')],
        ];
        //
        $doc = DomBuilder::create()->text('test')->getDocument();
        yield 'works on text nodes' => [
            $doc->firstChild,
            ['foo', $doc->createElement('bar'), $doc->createComment('baz')],
        ];
        //
        $doc = DomBuilder::create()->comment('test')->getDocument();
        yield 'works on comment nodes' => [
            $doc->firstChild,
            ['foo', $doc->createElement('bar'), $doc->createComment('baz')],
        ];
        //
        $doc = DomBuilder::create()->tag('test')->getDocument();
        yield 'works when target is in nodes' => [
            $doc->firstChild,
            ['foo', $doc->firstChild, $doc->createComment('baz')],
        ];
    }
}
