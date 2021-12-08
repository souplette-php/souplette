<?php declare(strict_types=1);

namespace Souplette\Tests\Dom;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\Element;
use Souplette\Dom\Node;

final class NodeTest extends TestCase
{
    /**
     * @dataProvider containsProvider
     */
    public function testContains(Node $parent, ?Node $target, bool $expected)
    {
        Assert::assertSame($expected, $parent->contains($target));
    }

    public function containsProvider(): iterable
    {
        yield 'returns true when document contains element' => [
            $doc = DomBuilder::html()
                ->tag('a')
                ->getDocument(),
            $doc->firstChild,
            true,
        ];
        yield 'returns false when document does not contain element' => [
            $doc = DomBuilder::html()
                ->tag('a')
                ->getDocument(),
            $doc->createElement('nope'),
            false,
        ];
        yield 'returns false when called with null' => [
            DomBuilder::html()->getDocument(),
            null,
            false,
        ];
        //
        $doc = DomBuilder::html()->tag('html')
            ->text('yep')
            ->getDocument();
        yield 'returns true when element contains node' => [
            $doc->firstChild,
            $doc->firstChild->firstChild,
            true,
        ];
        //
        $doc = DomBuilder::html()
            ->tag('html')
                ->comment('yep')
            ->close()
            ->comment('nope')
            ->getDocument();
        yield 'returns false when element does not contain node' => [
            $doc->firstChild,
            $doc->lastChild,
            false,
        ];
    }

    /**
     * @dataProvider parentElementProvider
     */
    public function testParentElement(Node $node, ?Element $expected)
    {
        Assert::assertSame($expected, $node->parentElement);
    }

    public function parentElementProvider(): iterable
    {
        yield 'returns null for document' => [
            DomBuilder::html()->getDocument(),
            null,
        ];
        yield 'returns null for document element' => [
            DomBuilder::html()->tag('html')->getDocument()->firstChild,
            null,
        ];
        yield 'returns null for document child comment' => [
            DomBuilder::html()->comment('nope')->getDocument()->firstChild,
            null,
        ];
        //
        $doc = DomBuilder::html()->tag('html')->tag('yep')->getDocument();
        yield 'returns an element parent element' => [
            $doc->firstChild->firstChild,
            $doc->firstChild,
        ];
        //
        $doc = DomBuilder::html()->tag('html')->text('yep')->getDocument();
        yield 'returns a text node parent element' => [
            $doc->firstChild->firstChild,
            $doc->firstChild,
        ];
        //
        $doc = DomBuilder::html()->tag('html')->comment('yep')->getDocument();
        yield 'returns a comment node parent element' => [
            $doc->firstChild->firstChild,
            $doc->firstChild,
        ];
        //
        $doc = DomBuilder::html()->tag('html')->tag('head')->tag('meta')->getDocument();
        yield 'also works nested' => [
            $doc->firstChild->firstChild->firstChild,
            $doc->firstChild->firstChild,
        ];
    }
}
