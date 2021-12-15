<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\DOM;

use Souplette\DOM\Document;
use Souplette\DOM\Element;
use Souplette\Tests\DOM\DOMBuilder;

/**
 * @see wpt/dom/common.js
 */
final class CommonProvider
{
    public static function testNodesShort(): \Traversable
    {
        $nodes = self::createNodes();
        yield 'paragraphs[0]' => $nodes['paragraphs'][0];
        yield 'paragraphs[0].firstChild' => $nodes['paragraphs'][0]->firstChild;
        yield 'paragraphs[1].firstChild' => $nodes['paragraphs'][1]->firstChild;
        yield 'foreignPara1' => $nodes['foreignPara1'];
        yield 'foreignPara1.firstChild' => $nodes['foreignPara1']->firstChild;
        yield 'detachedPara1' => $nodes['detachedPara1'];
        yield 'detachedPara1.firstChild' => $nodes['detachedPara1']->firstChild;
        yield 'document' => $nodes['document'];
        yield 'detachedDiv' => $nodes['detachedDiv'];
        yield 'foreignDoc' => $nodes['foreignDoc'];
        yield 'foreignPara2' => $nodes['foreignPara2'];
        yield 'xmlDoc' => $nodes['xmlDoc'];
        yield 'xmlElement' => $nodes['xmlElement'];
        yield 'detachedTextNode' => $nodes['detachedTextNode'];
        yield 'foreignTextNode' => $nodes['foreignTextNode'];
        yield 'processingInstruction' => $nodes['processingInstruction'];
        yield 'detachedProcessingInstruction' => $nodes['detachedProcessingInstruction'];
        yield 'comment' => $nodes['comment'];
        yield 'detachedComment' => $nodes['detachedComment'];
        yield 'docfrag' => $nodes['docfrag'];
        yield 'doctype' => $nodes['doctype'];
        yield 'foreignDoctype' => $nodes['foreignDoctype'];
    }

    public static function testNodes(): \Traversable
    {
        $nodes = self::createNodes();
        yield 'paragraphs[0]' => $nodes['paragraphs'][0];
        yield 'paragraphs[0].firstChild' => $nodes['paragraphs'][0]->firstChild;
        yield 'paragraphs[1].firstChild' => $nodes['paragraphs'][1]->firstChild;
        yield 'foreignPara1' => $nodes['foreignPara1'];
        yield 'foreignPara1.firstChild' => $nodes['foreignPara1']->firstChild;
        yield 'detachedPara1' => $nodes['detachedPara1'];
        yield 'detachedPara1.firstChild' => $nodes['detachedPara1']->firstChild;
        yield 'document' => $nodes['document'];
        yield 'detachedDiv' => $nodes['detachedDiv'];
        yield 'foreignDoc' => $nodes['foreignDoc'];
        yield 'foreignPara2' => $nodes['foreignPara2'];
        yield 'xmlDoc' => $nodes['xmlDoc'];
        yield 'xmlElement' => $nodes['xmlElement'];
        yield 'detachedTextNode' => $nodes['detachedTextNode'];
        yield 'foreignTextNode' => $nodes['foreignTextNode'];
        yield 'processingInstruction' => $nodes['processingInstruction'];
        yield 'detachedProcessingInstruction' => $nodes['detachedProcessingInstruction'];
        yield 'comment' => $nodes['comment'];
        yield 'detachedComment' => $nodes['detachedComment'];
        yield 'docfrag' => $nodes['docfrag'];
        yield 'doctype' => $nodes['doctype'];
        yield 'foreignDoctype' => $nodes['foreignDoctype'];
        //
        yield 'paragraphs[1]' => $nodes['paragraphs'][1];
        yield 'detachedPara2' => $nodes['detachedPara2'];
        yield 'detachedPara2.firstChild' => $nodes['detachedPara2']->firstChild;
        yield 'testDiv' => $nodes['testDiv'];
        yield 'detachedXmlElement' => $nodes['detachedXmlElement'];
        yield 'detachedForeignTextNode' => $nodes['detachedForeignTextNode'];
        yield 'xmlTextNode' => $nodes['xmlTextNode'];
        yield 'detachedXmlTextNode' => $nodes['detachedXmlTextNode'];
        yield 'xmlComment' => $nodes['xmlComment'];
        yield 'foreignComment' => $nodes['foreignComment'];
        yield 'detachedForeignComment' => $nodes['detachedForeignComment'];
        yield 'detachedXmlComment' => $nodes['detachedXmlComment'];
        yield 'foreignDocfrag' => $nodes['foreignDocfrag'];
        yield 'xmlDocfrag' => $nodes['xmlDocfrag'];
        yield 'xmlDoctype' => $nodes['xmlDoctype'];
    }

    private static function createNodes(): array
    {
        $document = DOMBuilder::html()
            ->doctype('html')
            ->tag('div')->id('test')
            ->getDocument();
        $testDiv = $document->documentElement;

        $paragraphs = [
            self::createElement($document, 'p', ['id' => 'a'], [
                // Test some diacritics, to make sure we are using code units here
                // and not something like grapheme clusters.
                'textContent' => "A\u{0308}b\u{0308}c\u{0308}d\u{0308}e\u{0308}f\u{0308}g\u{0308}h\u{0308}\n",
            ]),
            self::createElement($document, 'p', ['id' => 'b', 'style' => 'display:none'], [
                'textContent' => "Ijklmnop\n",
            ]),
            self::createElement($document, 'p', ['id' => 'c'], ['textContent' => 'Qrstuvwx']),
            self::createElement($document, 'p', ['id' => 'd', 'style' => 'display:none'], [
                'textContent' => 'Yzabcdef',
            ]),
            self::createElement($document, 'p', ['id' => 'e', 'style' => 'display:none'], [
                'textContent' => 'Ghijklmn',
            ]),
        ];
        $testDiv->append(...$paragraphs);

        $detachedDiv = $document->createElement("div");
        $detachedPara1 = $document->createElement("p");
        $detachedPara1->appendChild($document->createTextNode("Opqrstuv"));
        $detachedPara2 = $document->createElement("p");
        $detachedPara2->appendChild($document->createTextNode("Wxyzabcd"));
        $detachedDiv->appendChild($detachedPara1);
        $detachedDiv->appendChild($detachedPara2);

        $foreignDoc = $document->implementation->createHTMLDocument("");
        $foreignPara1 = $foreignDoc->createElement("p");
        $foreignPara1->appendChild($foreignDoc->createTextNode("Efghijkl"));
        $foreignPara2 = $foreignDoc->createElement("p");
        $foreignPara2->appendChild($foreignDoc->createTextNode("Mnopqrst"));
        $foreignDoc->body->appendChild($foreignPara1);
        $foreignDoc->body->appendChild($foreignPara2);
        // Now we get to do really silly stuff, which nobody in the universe is
        // ever going to actually do, but the spec defines behavior, so too bad.
        // Testing is fun!

        $xmlDoctype = $document->implementation->createDocumentType("qorflesnorf", "abcde", "x\"'y");
        $xmlDoc = $document->implementation->createDocument(null, null, $xmlDoctype);
        $detachedXmlElement = $xmlDoc->createElement("everyone-hates-hyphenated-element-names");
        $detachedTextNode = $document->createTextNode("Uvwxyzab");
        $detachedForeignTextNode = $foreignDoc->createTextNode("Cdefghij");
        $detachedXmlTextNode = $xmlDoc->createTextNode("Klmnopqr");
        // PIs only exist in XML documents, so don't bother with document or foreignDoc.
        $detachedProcessingInstruction = $xmlDoc->createProcessingInstruction("whippoorwill", "chirp chirp chirp");
        $detachedComment = $document->createComment("Stuvwxyz");
        // Hurrah, we finally got to "z" at the end!
        $detachedForeignComment = $foreignDoc->createComment("אריה יהודה");
        $detachedXmlComment = $xmlDoc->createComment("בן חיים אליעזר");

        // We should also test with document fragments that actually contain stuff
        // . . . but, maybe later.
        $docfrag = $document->createDocumentFragment();
        $foreignDocfrag = $foreignDoc->createDocumentFragment();
        $xmlDocfrag = $xmlDoc->createDocumentFragment();

        $xmlElement = $xmlDoc->createElement("igiveuponcreativenames");
        $xmlTextNode = $xmlDoc->createTextNode("do re mi fa so la ti");
        $xmlElement->appendChild($xmlTextNode);
        $processingInstruction = $xmlDoc->createProcessingInstruction("somePI", 'Did you know that ":syn sync fromstart" is very useful when using vim to edit large amounts of JavaScript embedded in HTML?');
        $xmlDoc->appendChild($xmlElement);
        $xmlDoc->appendChild($processingInstruction);
        $xmlComment = $xmlDoc->createComment("I maliciously created a comment that will break incautious XML serializers, but Firefox threw an exception, so all I got was this lousy T-shirt");
        $xmlDoc->appendChild($xmlComment);

        $comment = $document->createComment("Alphabet soup?");
        $testDiv->appendChild($comment);

        $foreignComment = $foreignDoc->createComment('"Commenter" and "commentator" mean different things.  I\'ve seen non-native speakers trip up on this.');
        $foreignDoc->appendChild($foreignComment);
        $foreignTextNode = $foreignDoc->createTextNode("I admit that I harbor doubts about whether we really need so many things to test, but it's too late to stop now.");
        $foreignDoc->body->appendChild($foreignTextNode);

        $doctype = $document->doctype;
        $foreignDoctype = $foreignDoc->doctype;

        $vars = get_defined_vars();
        foreach ($GLOBALS as $k => $v) unset($vars[$k]);
        
        return $vars;
    }

    private static function createElement(Document $doc, string $type, array $attrs = [], array $props = []): Element
    {
        $el = $doc->createElement($type);
        foreach ($attrs as $name => $value) {
            $el->setAttribute($name, $value);
        }
        foreach ($props as $name => $value) {
            $el->{$name} = $value;
        }
        return $el;
    }
}
