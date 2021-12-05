<?php declare(strict_types=1);

namespace Souplette\Tests\Dom;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\DocumentModes;
use Souplette\Dom\Node\Document;
use Souplette\Dom\Node\DocumentType;
use Souplette\Dom\Node\Element;
use Souplette\Dom\Node\Implementation;

final class DomImplementationTest extends TestCase
{
    public function testCreateHTMLDocument()
    {
        $dom = new Implementation();
        $doc = $dom->createHTMLDocument();
        // document
        Assert::assertInstanceOf(Document::class, $doc);
        Assert::assertSame(DocumentModes::NO_QUIRKS, $doc->mode);
        Assert::assertSame(Document::COMPAT_MODE_CSS1, $doc->compatMode);
        // doctype
        $doctype = $doc->firstChild;
        Assert::assertInstanceOf(DocumentType::class, $doctype);
        Assert::assertSame('html', $doctype->name);
        Assert::assertSame($doc->doctype, $doctype);
        // document element
        Assert::assertInstanceOf(Element::class, $doc->documentElement);
        Assert::assertSame('html', $doc->documentElement->tagName);
        // head
        $head = $doc->documentElement->firstChild;
        Assert::assertInstanceOf(Element::class, $head);
        Assert::assertSame('head', $head->tagName);
        Assert::assertSame($doc->head, $head);
        // <meta charset="UTF-8">
        $meta = $head->firstChild;
        Assert::assertInstanceOf(Element::class, $meta);
        Assert::assertSame('meta', $meta->tagName);
        Assert::assertSame('UTF-8', $meta->getAttribute('charset'));
        // body
        $body = $head->nextSibling;
        Assert::assertInstanceOf(Element::class, $body);
        Assert::assertSame('body', $body->tagName);
        Assert::assertSame($doc->body, $body);
    }
}
