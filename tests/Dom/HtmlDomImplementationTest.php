<?php declare(strict_types=1);

namespace Souplette\Tests\Dom;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\DocumentModes;
use Souplette\Dom\Document;
use Souplette\Dom\HtmlDomImplementation;
use Souplette\Dom\HtmlElement;

final class HtmlDomImplementationTest extends TestCase
{
    public function testCreateShell()
    {
        $dom = new HtmlDomImplementation();
        $doc = $dom->createShell();
        // document
        Assert::assertInstanceOf(Document::class, $doc);
        Assert::assertSame(DocumentModes::NO_QUIRKS, $doc->mode);
        Assert::assertSame(Document::COMPAT_MODE_CSS1, $doc->compatMode);
        // doctype
        $doctype = $doc->firstChild;
        Assert::assertInstanceOf(\DOMDocumentType::class, $doctype);
        Assert::assertSame('html', $doctype->name);
        Assert::assertSame($doc->doctype, $doctype);
        // document element
        Assert::assertInstanceOf(HtmlElement::class, $doc->documentElement);
        Assert::assertSame('html', $doc->documentElement->tagName);
        // head
        $head = $doc->documentElement->firstChild;
        Assert::assertInstanceOf(HtmlElement::class, $head);
        Assert::assertSame('head', $head->tagName);
        Assert::assertSame($doc->head, $head);
        // <meta charset="UTF-8">
        $meta = $head->firstChild;
        Assert::assertInstanceOf(HtmlElement::class, $meta);
        Assert::assertSame('meta', $meta->tagName);
        Assert::assertSame('UTF-8', $meta->getAttribute('charset'));
        // body
        $body = $head->nextSibling;
        Assert::assertInstanceOf(HtmlElement::class, $body);
        Assert::assertSame('body', $body->tagName);
        Assert::assertSame($doc->body, $body);
    }
}
