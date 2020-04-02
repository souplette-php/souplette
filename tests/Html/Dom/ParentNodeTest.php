<?php declare(strict_types=1);

namespace JoliPotage\Tests\Html\Dom;

use JoliPotage\Html\Dom\Api\ParentNodeInterface;
use JoliPotage\Tests\Html\DomBuilder;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class ParentNodeTest extends TestCase
{
    /**
     * @dataProvider childrenProvider
     * @param ParentNodeInterface $parent
     * @param array $expected
     */
    public function testChildren(ParentNodeInterface $parent, array $expected)
    {
        $children = $parent->children;
        Assert::assertCount(count($expected), $children);
        foreach ($expected as $i => $child) {
            Assert::assertSame($child, $children[$i]->tagName);
        }
    }

    public function childrenProvider()
    {
        $doc = DomBuilder::create()
            ->text('foo')
            ->tag('a')->close()
            ->comment('bar')
            ->tag('b')->close()
            ->text('baz')
            ->getDocument();
        yield 'works on document' => [$doc, ['a', 'b']];
        //
        $doc = DomBuilder::create()
            ->tag('html')
                ->text('foo')
                ->tag('a')->close()
                ->comment('bar')
                ->tag('b')->close()
                ->text('baz')
            ->getDocument();
        yield 'works on element' => [$doc->firstChild, ['a', 'b']];
    }

    /**
     * @dataProvider firstElementChildProvider
     * @param ParentNodeInterface $parent
     * @param $expected
     */
    public function testFirstElementChild(ParentNodeInterface $parent, $expected)
    {
        Assert::assertSame($expected, $parent->firstElementChild);
    }

    public function firstElementChildProvider()
    {
        $doc = DomBuilder::create()
            ->text('foo')
            ->comment('bar')
            ->tag('baz')->close()
            ->tag('qux')->close()
            ->getDocument();
        yield 'works on document' => [$doc, $doc->lastChild->previousSibling];
        //
        $doc = DomBuilder::create()->tag('html')
            ->text('foo')
            ->comment('bar')
            ->tag('baz')->close()
            ->tag('qux')->close()
            ->getDocument();
        yield 'works on element' => [$doc->firstChild, $doc->firstChild->lastChild->previousSibling];
    }

    /**
     * @dataProvider lastElementChildProvider
     * @param ParentNodeInterface $parent
     * @param $expected
     */
    public function testLastElementChild(ParentNodeInterface $parent, $expected)
    {
        Assert::assertSame($expected, $parent->lastElementChild);
    }

    public function lastElementChildProvider()
    {
        $doc = DomBuilder::create()
            ->tag('foo')->close()
            ->text('bar')
            ->tag('baz')->close()
            ->comment('qux')
            ->getDocument();
        yield 'works on document' => [$doc, $doc->lastChild->previousSibling];
        //
        $doc = DomBuilder::create()->tag('html')
            ->tag('foo')->close()
            ->text('bar')
            ->tag('baz')->close()
            ->comment('qux')
            ->getDocument();
        yield 'works on element' => [$doc->firstChild, $doc->firstChild->lastChild->previousSibling];
    }

    /**
     * @dataProvider prependProvider
     * @param $parent
     * @param $nodes
     * @param $expectedChildren
     */
    public function testPrepend($parent, $nodes, $expectedChildren)
    {
        $parent->prepend(...$nodes);
        Assert::assertCount(count($expectedChildren), $parent->childNodes);
        $child = $parent->firstChild;
        foreach ($expectedChildren as $expectedChild) {
            if (is_string($expectedChild)) {
                Assert::assertSame($expectedChild, $child->data);
            } else {
                Assert::assertSame($expectedChild, $child);
            }
            $child = $child->nextSibling;
        }
    }

    public function prependProvider()
    {
        $doc = DomBuilder::create()
            ->tag('a')->close()
            ->text('foo')
            ->getDocument();
        yield 'works on document' => [
            $doc,
            $nodes = ['bar', $doc->createElement('b'), $doc->createComment('baz')],
            [...$nodes, $doc->firstChild, $doc->lastChild],
        ];
        //
        $doc = DomBuilder::create()->tag('html')
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
     * @param $parent
     * @param $nodes
     * @param $expectedChildren
     */
    public function testAppend($parent, $nodes, $expectedChildren)
    {
        $parent->append(...$nodes);
        Assert::assertCount(count($expectedChildren), $parent->childNodes);
        $child = $parent->firstChild;
        foreach ($expectedChildren as $expectedChild) {
            if (is_string($expectedChild)) {
                Assert::assertSame($expectedChild, $child->data);
            } else {
                Assert::assertSame($expectedChild, $child);
            }
            $child = $child->nextSibling;
        }
    }

    public function appendProvider()
    {
        $doc = DomBuilder::create()
            ->tag('a')->close()
            ->text('foo')
            ->getDocument();
        yield 'works on document' => [
            $doc,
            $nodes = ['bar', $doc->createElement('b'), $doc->createComment('baz')],
            [$doc->firstChild, $doc->lastChild, ...$nodes],
        ];
        //
        $doc = DomBuilder::create()->tag('html')
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
