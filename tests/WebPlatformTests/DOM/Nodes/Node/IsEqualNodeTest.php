<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\DOM\Nodes\Node;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Souplette\DOM\Comment;
use Souplette\DOM\Document;
use Souplette\DOM\DocumentFragment;
use Souplette\DOM\DocumentType;
use Souplette\DOM\Element;
use Souplette\DOM\Implementation;
use Souplette\DOM\Namespaces;
use Souplette\DOM\Node;
use Souplette\DOM\ProcessingInstruction;
use Souplette\DOM\Text;

/**
 * Ported from web-platform-tests
 * wpt/dom/nodes/Node-isEqualNode.html
 */
final class IsEqualNodeTest extends TestCase
{
    #[DataProvider('documentTypeProvider')]
    public function testDocumentType(DocumentType $node, DocumentType $other, bool $expected)
    {
        Assert::assertSame($expected, $node->isEqualNode($other));
    }

    public static function documentTypeProvider(): iterable
    {
        $impl = new Implementation();
        $doctype1 = $impl->createDocumentType('qname', 'pubId', 'sysId');
        $doctype2 = $impl->createDocumentType('qname', 'pubId', 'sysId');
        $doctype3 = $impl->createDocumentType('qname2', 'pubId', 'sysId');
        $doctype4 = $impl->createDocumentType('qname', 'pubId2', 'sysId');
        $doctype5 = $impl->createDocumentType('qname', 'pubId', 'sysId3');

        yield 'self-comparison' => [$doctype1, $doctype1, true];
        yield 'same properties' => [$doctype1, $doctype2, true];
        yield 'different name' => [$doctype1, $doctype3, false];
        yield 'different publicId' => [$doctype1, $doctype4, false];
        yield 'different systemId' => [$doctype1, $doctype5, false];
    }

    #[DataProvider('elementProvider')]
    public function testElement(Element $node, Element $other, bool $expected)
    {
        Assert::assertSame($expected, $node->isEqualNode($other));
    }

    public static function elementProvider(): iterable
    {
        $doc = new Document();
        $element1 = $doc->createElementNS('namespace', 'prefix:localName');
        $element2 = $doc->createElementNS('namespace', 'prefix:localName');
        $element3 = $doc->createElementNS('namespace2', 'prefix:localName');
        $element4 = $doc->createElementNS('namespace', 'prefix2:localName');
        $element5 = $doc->createElementNS('namespace', 'prefix:localName2');

        $element6 = $doc->createElementNS('namespace', 'prefix:localName');
        $element6->setAttribute('foo', '');

        yield 'self-comparison' => [$element1, $element1, true];
        yield 'same properties' => [$element1, $element2, true];
        yield 'different namespace' => [$element1, $element3, false];
        yield 'different prefix' => [$element1, $element4, false];
        yield 'different local name' => [$element1, $element5, false];
        yield 'different number of attributes' => [$element1, $element6, false];
    }

    #[DataProvider('attributesProvider')]
    public function testAttributes(Element $node, Element $other, bool $expected): void
    {
        Assert::assertSame($expected, $node->isEqualNode($other));
    }

    public static function attributesProvider()
    {
        $doc = new Document();
        $element1 = $doc->createElement('element');
        $element1->setAttributeNS('namespace', 'prefix:localName', 'value');

        $element2 = $doc->createElement('element');
        $element2->setAttributeNS('namespace', 'prefix:localName', 'value');

        $element3 = $doc->createElement('element');
        $element3->setAttributeNS('namespace2', 'prefix:localName', 'value');

        $element4 = $doc->createElement('element');
        $element4->setAttributeNS('namespace', 'prefix2:localName', 'value');

        $element5 = $doc->createElement('element');
        $element5->setAttributeNS('namespace', 'prefix:localName2', 'value');

        $element6 = $doc->createElement('element');
        $element6->setAttributeNS('namespace', 'prefix:localName', 'value2');

        yield 'self-comparison' => [$element1, $element1, true];
        yield 'attribute with same properties' => [$element1, $element2, true];
        yield 'attribute with different namespace' => [$element1, $element3, false];
        yield 'attribute with different prefix' => [$element1, $element4, true];
        yield 'attribute with different local name' => [$element1, $element5, false];
        yield 'attribute with different value' => [$element1, $element6, false];
    }

    #[DataProvider('processingInstructionProvider')]
    public function testProcessingInstruction(
        ProcessingInstruction $node,
        ProcessingInstruction $other,
        bool $expected
    ): void {
        Assert::assertSame($expected, $node->isEqualNode($other));
    }

    public static function processingInstructionProvider(): iterable
    {
        $doc = new Document();
        $pi1 = $doc->createProcessingInstruction('target', 'data');
        $pi2 = $doc->createProcessingInstruction('target', 'data');
        $pi3 = $doc->createProcessingInstruction('target2', 'data');
        $pi4 = $doc->createProcessingInstruction('target', 'data2');

        yield 'self-comparison' => [$pi1, $pi1, true];
        yield 'same properties' => [$pi1, $pi2, true];
        yield 'different target' => [$pi1, $pi3, false];
        yield 'different data' => [$pi1, $pi4, false];
    }

    #[DataProvider('textProvider')]
    public function testText(Text $node, Text $other, bool $expected): void
    {
        Assert::assertSame($expected, $node->isEqualNode($other));
    }

    public static function textProvider(): iterable
    {
        $doc = new Document();
        $text1 = $doc->createTextNode('data');
        $text2 = $doc->createTextNode('data');
        $text3 = $doc->createTextNode('data2');

        yield 'self-comparison' => [$text1, $text1, true];
        yield 'same properties' => [$text1, $text2, true];
        yield 'different data' => [$text1, $text3, false];
    }

    #[DataProvider('commentProvider')]
    public function testComment(Comment $node, Comment $other, bool $expected): void
    {
        Assert::assertSame($expected, $node->isEqualNode($other));
    }

    public static function commentProvider(): iterable
    {
        $doc = new Document();
        $comment1 = $doc->createComment('data');
        $comment2 = $doc->createComment('data');
        $comment3 = $doc->createComment('data2');

        yield 'self-comparison' => [$comment1, $comment1, true];
        yield 'same properties' => [$comment1, $comment2, true];
        yield 'different data' => [$comment1, $comment3, false];
    }

    #[DataProvider('documentFragmentProvider')]
    public function testDocumentFragment(DocumentFragment $node, DocumentFragment $other, bool $expected): void
    {
        Assert::assertSame($expected, $node->isEqualNode($other));
    }

    public static function documentFragmentProvider(): iterable
    {
        $doc = new Document();
        $documentFragment1 = $doc->createDocumentFragment();
        $documentFragment2 = $doc->createDocumentFragment();

        yield 'self-comparison' => [$documentFragment1, $documentFragment1, true];
        yield 'same properties' => [$documentFragment1, $documentFragment2, true];
    }

    #[DataProvider('documentProvider')]
    public function testDocument(Document $node, Document $other, bool $expected): void
    {
        Assert::assertSame($expected, $node->isEqualNode($other));
    }

    public static function documentProvider(): iterable
    {
        $impl = new Implementation();
        $doc1 = $impl->createDocument('', '');
        $doc2 = $impl->createDocument('', '');

        yield 'self-comparison' => [$doc1, $doc1, true];
        yield 'another empty XML document' => [$doc1, $doc2, true];

        $htmlDoctype = $impl->createDocumentType('html');
        $doc3 = $impl->createDocument(Namespaces::HTML, 'html', $htmlDoctype);
        $doc3->documentElement->appendChild($doc3->createElement("head"));
        $doc3->documentElement->appendChild($doc3->createElement("body"));
        $doc4 = $impl->createHTMLDocument();

        yield 'default HTML documents, created different ways' => [$doc3, $doc4, true];
    }

    #[DataProvider('deepEqualityProvider')]
    public function testDeepEquality(Node $node, Node $other, bool $expected): void
    {
        Assert::assertSame($expected, $node->isEqualNode($other));
    }

    public static function deepEqualityProvider(): iterable
    {
        $doc = new Document();
        $factory = static function(callable $parentFactory) use ($doc) {
            $parentA = $parentFactory();
            $parentB = $parentFactory();
            $parentC = $parentFactory();

            $parentA->appendChild($doc->createComment(''));
            yield [$parentA, $parentB, false];
            $parentC->appendChild($doc->createComment(''));
            yield [$parentA, $parentC, true];
        };

        yield from $factory(fn() => $doc->createElement('foo'));
        yield from $factory(fn() => $doc->createDocumentFragment());
        yield from $factory(fn() => $doc->implementation->createDocument('', ''));
        yield from $factory(fn() => $doc->implementation->createHTMLDocument());
    }
}
