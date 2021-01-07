<?php declare(strict_types=1);

namespace Souplette\Tests\Html\Dom;

use Souplette\Html\Dom\Api\HtmlNodeInterface;
use Souplette\Tests\Html\DomBuilder;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class HtmlNodeTest extends TestCase
{
    /**
     * @dataProvider containsProvider
     * @param $parent
     * @param $target
     * @param $expected
     */
    public function testContains(HtmlNodeInterface $parent, $target, $expected)
    {
        Assert::assertSame($expected, $parent->contains($target));
    }

    public function containsProvider()
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
     * @param $node
     * @param $expected
     */
    public function testParentElement(HtmlNodeInterface $node, $expected)
    {
        Assert::assertSame($expected, $node->parentElement);
    }

    public function parentElementProvider()
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
        yield 'returns null for document child text' => [
            DomBuilder::create()->text('nope')->getDocument()->firstChild,
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
