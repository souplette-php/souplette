<?php declare(strict_types=1);

namespace Souplette\Tests\DOM\Traversal;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\DOM\Node;
use Souplette\DOM\Traversal\NodeTraversal;
use Souplette\Tests\DOM\DOMBuilder;

final class NodeTraversalTest extends TestCase
{
    public function testNext()
    {
        $doc = DOMBuilder::html()->tag('html')
            ->tag('div')->id('c0')
                ->tag('div')->id('c00')->close()
                ->tag('div')->id('c01')->close()
            ->close()
            ->tag('div')->id('c1')
                ->tag('div')->id('c10')->close()
            ->getDocument();
        $root = $doc->documentElement;
        $c0 = $root->firstChild;
        $c00 = $c0->firstChild;
        $c01 = $c00->nextSibling;
        $c1 = $c0->nextSibling;
        $c10 = $c1->firstChild;

        Assert::assertSame($c0, NodeTraversal::next($root));
        Assert::assertSame($c00, NodeTraversal::next($c0));
        Assert::assertSame($c01, NodeTraversal::next($c00));
        Assert::assertSame($c1, NodeTraversal::next($c01));
        Assert::assertSame($c10, NodeTraversal::next($c1));
        Assert::assertNull(NodeTraversal::next($c10));
    }

    public function testNextPostOrder()
    {
        $doc = DOMBuilder::html()->tag('html')
            ->tag('div')->id('c0')
                ->tag('div')->id('c00')->close()
                ->tag('div')->id('c01')->close()
            ->close()
            ->tag('div')->id('c1')
                ->tag('div')->id('c10')->close()
            ->getDocument();
        $root = $doc->documentElement;
        $c0 = $root->firstChild;
        $c00 = $c0->firstChild;
        $c01 = $c00->nextSibling;
        $c1 = $c0->nextSibling;
        $c10 = $c1->firstChild;

        Assert::assertSame($doc, NodeTraversal::nextPostOrder($root));
        Assert::assertSame($c10, NodeTraversal::nextPostOrder($c0));
        Assert::assertSame($root, NodeTraversal::nextPostOrder($c1));
        Assert::assertSame($c01, NodeTraversal::nextPostOrder($c00));
        Assert::assertSame($c0, NodeTraversal::nextPostOrder($c01));
        Assert::assertSame($c1, NodeTraversal::nextPostOrder($c10));
    }

    public function testPrevious()
    {
        $doc = DOMBuilder::html()->tag('html')
            ->tag('div')->id('c0')
                ->tag('div')->id('c00')->close()
                ->tag('div')->id('c01')->close()
            ->close()
            ->tag('div')->id('c1')
                ->tag('div')->id('c10')->close()
            ->getDocument();
        $root = $doc->documentElement;
        $c0 = $root->firstChild;
        $c00 = $c0->firstChild;
        $c01 = $c00->nextSibling;
        $c1 = $c0->nextSibling;
        $c10 = $c1->firstChild;

        Assert::assertSame($doc, NodeTraversal::previous($root));
        Assert::assertSame($root, NodeTraversal::previous($c0));
        Assert::assertSame($c0, NodeTraversal::previous($c00));
        Assert::assertSame($c00, NodeTraversal::previous($c01));
        Assert::assertSame($c01, NodeTraversal::previous($c1));
        Assert::assertSame($c1, NodeTraversal::previous($c10));
    }

    public function testPreviousPostOrder()
    {
        $doc = DOMBuilder::html()->tag('html')
            ->tag('div')->id('c0')
                ->tag('div')->id('c00')->close()
                ->tag('div')->id('c01')->close()
            ->close()
            ->tag('div')->id('c1')
                ->tag('div')->id('c10')->close()
            ->getDocument();
        $root = $doc->documentElement;
        $c0 = $root->firstChild;
        $c00 = $c0->firstChild;
        $c01 = $c00->nextSibling;
        $c1 = $c0->nextSibling;
        $c10 = $c1->firstChild;

        Assert::assertSame($c1, NodeTraversal::previousPostOrder($root));
        Assert::assertSame($c01, NodeTraversal::previousPostOrder($c0));
        Assert::assertSame($c10, NodeTraversal::previousPostOrder($c1));
        Assert::assertSame(null, NodeTraversal::previousPostOrder($c00));
        Assert::assertSame($c00, NodeTraversal::previousPostOrder($c01));
        Assert::assertSame($c0, NodeTraversal::previousPostOrder($c10));
    }

    /**
     * @dataProvider commonAncestorProvider
     */
    public function testCommonAncestor(?Node $expected, Node $a, Node $b)
    {
        Assert::assertSame($expected, NodeTraversal::commonAncestor($a, $b));
        Assert::assertSame($expected, NodeTraversal::commonAncestor($b, $a));
    }

    public function commonAncestorProvider(): iterable
    {
        $doc = DOMBuilder::html()->tag('html')
            ->tag('div')->id('c0')
                ->tag('div')->id('c00')
                    ->tag('div')->id('c000')->close()
                ->close()
                ->tag('div')->id('c01')->close()
            ->close()
            ->tag('div')->id('c1')
                ->tag('div')->id('c10')->close()
            ->close()
            ->tag('div')->id('c2')->close()
            ->getDocument();

        $root = $doc->documentElement;
        $c0 = $root->firstChild;
        $c1 = $c0->nextSibling;
        $c2 = $c1->nextSibling;

        $c00 = $c0->firstChild;
        $c000 = $c00->firstChild;
        $c01 = $c00->nextSibling;
        $c10 = $c1->firstChild;

        yield [$root, $c0, $c1];
        yield [$root, $c1, $c2];
        yield [$root, $c00, $c10];
        yield [$root, $c01, $c10];
        yield [$root, $c2, $c10];
        yield [$root, $c2, $c000];

        yield [$c0, $c00, $c01];
        yield [$c0, $c000, $c01];
        yield [$c1, $c1, $c10];

        $outsider = $doc->createElement('div');
        yield [null, $c2, $outsider];
    }
}
