<?php declare(strict_types=1);

namespace Souplette\Tests\DOM;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\DOM\Element;
use Souplette\DOM\ParentNode;

final class ParentNodeTest extends TestCase
{
    /**
     * @dataProvider childrenProvider
     */
    public function testChildren(ParentNode $parent, array $expected)
    {
        $children = $parent->children;
        Assert::assertCount(\count($expected), $children);
        foreach ($expected as $i => $child) {
            Assert::assertSame($child, $children[$i]->nodeName);
        }
    }

    public function childrenProvider(): iterable
    {
        $doc = DOMBuilder::html()
            ->comment('bar')
            ->tag('a')->close()
            ->comment('bar')
            ->getDocument();
        yield 'works on document' => [$doc, ['A']];
        //
        $doc = DOMBuilder::html()
            ->tag('html')
                ->text('foo')
                ->tag('a')->close()
                ->comment('bar')
                ->tag('b')->close()
                ->text('baz')
            ->getDocument();
        yield 'works on element' => [$doc->firstChild, ['A', 'B']];
    }

    /**
     * @dataProvider firstElementChildProvider
     */
    public function testFirstElementChild(ParentNode $parent, Element $expected)
    {
        Assert::assertSame($expected, $parent->firstElementChild);
    }

    public function firstElementChildProvider(): iterable
    {
        $doc = DOMBuilder::html()
            ->comment('foo')
            ->tag('baz')->close()
            ->comment('bar')
            ->getDocument();
        yield 'works on document' => [$doc, $doc->lastChild->previousSibling];
        //
        $doc = DOMBuilder::html()->tag('html')
            ->text('foo')
            ->comment('bar')
            ->tag('baz')->close()
            ->tag('qux')->close()
            ->getDocument();
        yield 'works on element' => [$doc->firstChild, $doc->firstChild->lastChild->previousSibling];
    }

    /**
     * @dataProvider lastElementChildProvider
     */
    public function testLastElementChild(ParentNode $parent, Element $expected)
    {
        Assert::assertSame($expected, $parent->lastElementChild);
    }

    public function lastElementChildProvider(): iterable
    {
        $doc = DOMBuilder::html()
            ->comment('foo')
            ->tag('bar')->close()
            ->comment('baz')
            ->getDocument();
        yield 'works on document' => [$doc, $doc->lastChild->previousSibling];
        //
        $doc = DOMBuilder::html()->tag('html')
            ->tag('foo')->close()
            ->text('bar')
            ->tag('baz')->close()
            ->comment('qux')
            ->getDocument();
        yield 'works on element' => [$doc->firstChild, $doc->firstChild->lastChild->previousSibling];
    }

    /**
     * @dataProvider prependProvider
     */
    public function testPrepend($parent, $nodes, $expectedChildren)
    {
        $parent->prepend(...$nodes);
        Assert::assertCount(\count($expectedChildren), $parent->childNodes);
        $child = $parent->firstChild;
        foreach ($expectedChildren as $expectedChild) {
            if (\is_string($expectedChild)) {
                Assert::assertSame($expectedChild, $child->data);
            } else {
                Assert::assertSame($expectedChild, $child);
            }
            $child = $child->nextSibling;
        }
    }

    public function prependProvider(): iterable
    {
        $doc = DOMBuilder::html()
            ->tag('a')->close()
            ->comment('foo')
            ->getDocument();
        yield 'works on document' => [
            $doc,
            $nodes = [$doc->createComment('baz')],
            [...$nodes, $doc->firstChild, $doc->lastChild],
        ];
        //
        $doc = DOMBuilder::html()->tag('html')
            ->tag('a')->close()
            ->text('foo')
            ->getDocument();
        yield 'works on element' => [
            $html = $doc->firstChild,
            $nodes = ['bar', $doc->createElement('b'), $doc->createComment('baz')],
            [...$nodes, $html->firstChild, $html->lastChild],
        ];
    }

    /**
     * @dataProvider appendProvider
     */
    public function testAppend($parent, $nodes, $expectedChildren)
    {
        $parent->append(...$nodes);
        Assert::assertCount(\count($expectedChildren), $parent->childNodes);
        $child = $parent->firstChild;
        foreach ($expectedChildren as $expectedChild) {
            if (\is_string($expectedChild)) {
                Assert::assertSame($expectedChild, $child->data);
            } else {
                Assert::assertSame($expectedChild, $child);
            }
            $child = $child->nextSibling;
        }
    }

    public function appendProvider(): iterable
    {
        $doc = DOMBuilder::html()
            ->comment('foo')
            ->tag('a')->close()
            ->getDocument();
        yield 'works on document' => [
            $doc,
            $nodes = [$doc->createComment('bar')],
            [$doc->firstChild, $doc->lastChild, ...$nodes],
        ];
        //
        $doc = DOMBuilder::html()->tag('html')
            ->tag('a')->close()
            ->text('foo')
            ->getDocument();
        yield 'works on element' => [
            $html = $doc->firstChild,
            $nodes = ['bar', $doc->createElement('b'), $doc->createComment('baz')],
            [$html->firstChild, $html->lastChild, ...$nodes],
        ];
    }

    public function testQuerySelector()
    {
        $this->markTestIncomplete('Not implemented');
    }

    public function testQuerySelectorAll()
    {
        $this->markTestIncomplete('Not implemented');
    }
}
