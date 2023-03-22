<?php declare(strict_types=1);

namespace Souplette\Tests\DOM;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Souplette\DOM\Document;
use Souplette\DOM\Element;
use Souplette\DOM\Node;
use Souplette\Exception\UndefinedProperty;

final class NodeTest extends TestCase
{
    private static function createDummyNode(): Node
    {
        return new class extends Node {
            protected function clone(?Document $document, bool $deep = false): static {
                return new self();
            }
            public function isEqualNode(?Node $otherNode): bool {
                return false;
            }
        };
    }

    public function testItThrowsUndefinedPropertyForGetters()
    {
        $this->expectException(UndefinedProperty::class);
        $node = self::createDummyNode();
        $frobnicator = $node->snafucated;
    }

    public function testItThrowsUndefinedPropertyForSetters()
    {
        $this->expectException(UndefinedProperty::class);
        $node = self::createDummyNode();
        $node->snafucated = 'frobnicator';
    }

    #[DataProvider('containsProvider')]
    public function testContains(Node $parent, ?Node $target, bool $expected)
    {
        Assert::assertSame($expected, $parent->contains($target));
    }

    public static function containsProvider(): iterable
    {
        yield 'returns true when document contains element' => [
            $doc = DOMBuilder::html()
                ->tag('a')
                ->getDocument(),
            $doc->firstChild,
            true,
        ];
        yield 'returns false when document does not contain element' => [
            $doc = DOMBuilder::html()
                ->tag('a')
                ->getDocument(),
            $doc->createElement('nope'),
            false,
        ];
        yield 'returns false when called with null' => [
            DOMBuilder::html()->getDocument(),
            null,
            false,
        ];
        //
        $doc = DOMBuilder::html()->tag('html')
            ->text('yep')
            ->getDocument();
        yield 'returns true when element contains node' => [
            $doc->firstChild,
            $doc->firstChild->firstChild,
            true,
        ];
        //
        $doc = DOMBuilder::html()
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

    #[DataProvider('parentElementProvider')]
    public function testParentElement(Node $node, ?Element $expected)
    {
        Assert::assertSame($expected, $node->parentElement);
    }

    public static function parentElementProvider(): iterable
    {
        yield 'returns null for document' => [
            DOMBuilder::html()->getDocument(),
            null,
        ];
        yield 'returns null for document element' => [
            DOMBuilder::html()->tag('html')->getDocument()->firstChild,
            null,
        ];
        yield 'returns null for document child comment' => [
            DOMBuilder::html()->comment('nope')->getDocument()->firstChild,
            null,
        ];
        //
        $doc = DOMBuilder::html()->tag('html')->tag('yep')->getDocument();
        yield 'returns an element parent element' => [
            $doc->firstChild->firstChild,
            $doc->firstChild,
        ];
        //
        $doc = DOMBuilder::html()->tag('html')->text('yep')->getDocument();
        yield 'returns a text node parent element' => [
            $doc->firstChild->firstChild,
            $doc->firstChild,
        ];
        //
        $doc = DOMBuilder::html()->tag('html')->comment('yep')->getDocument();
        yield 'returns a comment node parent element' => [
            $doc->firstChild->firstChild,
            $doc->firstChild,
        ];
        //
        $doc = DOMBuilder::html()->tag('html')->tag('head')->tag('meta')->getDocument();
        yield 'also works nested' => [
            $doc->firstChild->firstChild->firstChild,
            $doc->firstChild->firstChild,
        ];
    }

    public function testNormalize()
    {
        $doc = DOMBuilder::html()->tag('html')
            ->text('foo')
            ->text('')
            ->text('bar')
            ->tag('p')
                ->text('baz')
                ->text('')
                ->text('qux')
            ->getDocument();
        $root = $doc->documentElement;
        $para = $root->lastElementChild;
        $doc->normalize();
        Assert::assertCount(2, $root->childNodes);
        Assert::assertSame('foobar', $root->firstChild->textContent);
        Assert::assertCount(1, $para->childNodes);
        Assert::assertSame('bazqux', $para->firstChild->textContent);
    }
}
