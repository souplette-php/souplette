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
            $doc = DomBuilder::create()
                ->tag('a')
                ->getDocument(),
            $doc->firstChild,
            true,
        ];
        yield 'returns false when document does not contain element' => [
            $doc = DomBuilder::create()
                ->tag('a')
                ->getDocument(),
            $doc->createElement('nope'),
            false,
        ];
        yield 'returns false when called with null' => [
            DomBuilder::create()->getDocument(),
            null,
            false,
        ];
        //
        $doc = DomBuilder::create()->tag('html')
            ->text('yep')
            ->getDocument();
        yield 'returns true when element contains node' => [
            $doc->firstChild,
            $doc->firstChild->firstChild,
            true,
        ];
        //
        $doc = DomBuilder::create()
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
            DomBuilder::create()->getDocument(),
            null,
        ];
        yield 'returns null for document element' => [
            DomBuilder::create()->tag('html')->getDocument()->firstChild,
            null,
        ];
        yield 'returns null for document child comment' => [
            DomBuilder::create()->comment('nope')->getDocument()->firstChild,
            null,
        ];
        //
        $doc = DomBuilder::create()->tag('html')->tag('yep')->getDocument();
        yield 'returns an element parent element' => [
            $doc->firstChild->firstChild,
            $doc->firstChild,
        ];
        //
        $doc = DomBuilder::create()->tag('html')->text('yep')->getDocument();
        yield 'returns a text node parent element' => [
            $doc->firstChild->firstChild,
            $doc->firstChild,
        ];
        //
        $doc = DomBuilder::create()->tag('html')->comment('yep')->getDocument();
        yield 'returns a comment node parent element' => [
            $doc->firstChild->firstChild,
            $doc->firstChild,
        ];
        //
        $doc = DomBuilder::create()->tag('html')->tag('head')->tag('meta')->getDocument();
        yield 'also works nested' => [
            $doc->firstChild->firstChild->firstChild,
            $doc->firstChild->firstChild,
        ];
    }
}
