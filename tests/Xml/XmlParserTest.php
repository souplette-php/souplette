<?php declare(strict_types=1);

namespace Souplette\Tests\Xml;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\CDATASection;
use Souplette\Dom\Comment;
use Souplette\Dom\Element;
use Souplette\Dom\Namespaces;
use Souplette\Dom\Node;
use Souplette\Dom\ProcessingInstruction;
use Souplette\Dom\Text;
use Souplette\Dom\XmlDocument;
use Souplette\Souplette;
use Souplette\Tests\Dom\DomBuilder;
use Souplette\Xml\Parser\ExternalEntityLoaderInterface;
use Souplette\Xml\XmlParser;

final class XmlParserTest extends TestCase
{
    public function testBasicTree()
    {
        $doc = Souplette::parseXml('<root/>', 'text/xml');
        Assert::assertInstanceOf(XmlDocument::class, $doc);
        Assert::assertSame('text/xml', $doc->contentType);
        Assert::assertSame('UTF-8', $doc->characterSet);
        Assert::assertSame('UTF-8', $doc->charset);
        Assert::assertSame('1.0', $doc->xmlVersion);
        Assert::assertFalse($doc->xmlStandalone);
        $root = $doc->documentElement;
        Assert::assertInstanceOf(Element::class, $root);
        Assert::assertSame('root', $root->localName);
    }

    public function testXmlDeclaration()
    {
        $doc = Souplette::parseXml(
            '<?xml version="1.0" encoding="windows-1252" standalone="yes"?><root/>',
            'text/xml'
        );
        Assert::assertSame('1.0', $doc->xmlVersion);
        Assert::assertSame('windows-1252', $doc->inputEncoding);
        Assert::assertTrue($doc->xmlStandalone);
        $root = $doc->documentElement;
        Assert::assertInstanceOf(Element::class, $root);
        Assert::assertSame('root', $root->localName);
    }

    public function testEncoding()
    {
        $input = file_get_contents(__DIR__ . '/../resources/encoding/x80_windows-1252.xml');
        // "€" is \x80 in windows-1252, \x20AC in utf-8
        // If the following assertion fails, somebody has change the encoding...
        Assert::assertTrue(str_contains($input, "\x80"));
        $doc = Souplette::parseXml($input, 'text/xml');
        Assert::assertSame('windows-1252', $doc->inputEncoding);
        Assert::assertSame('UTF-8', $doc->characterSet);
        Assert::assertSame("\u{20AC}", $doc->documentElement->textContent);
    }

    public function testDoctypeName()
    {
        $doc = Souplette::parseXml(
            '<!DOCTYPE foo><root/>',
            'text/xml'
        );
        Assert::assertSame('foo', $doc->doctype->name);
    }

    public function testDoctypeSystem()
    {
        $loader = new class implements ExternalEntityLoaderInterface {
            public function __invoke(?string $publicId, string $systemId, array $context)
            {
                $fp = fopen('php://memory', 'r+');
                fwrite($fp, '<!ENTITY poo "&#x1F4A9;">');
                rewind($fp);
                return $fp;
            }
        };
        $parser = (new XmlParser())->withExternalEntityLoader($loader);
        $doc = $parser->parse('<!DOCTYPE poo SYSTEM "urn:bar"><pile-of>&poo;</pile-of>');
        $doctype = $doc->doctype;
        Assert::assertSame('poo', $doctype->name);
        Assert::assertSame('', $doctype->publicId);
        Assert::assertSame('urn:bar', $doctype->systemId);
        Assert::assertSame('💩', $doc->documentElement->textContent);
    }

    public function testDoctypePublicId()
    {
        $doc = Souplette::parseXml(
            '<!DOCTYPE foo PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" \'urn:bar\'><root/>',
            'text/xml'
        );
        Assert::assertSame('foo', $doc->doctype->name);
        Assert::assertSame('-//W3C//DTD XHTML 1.0 Transitional//EN', $doc->doctype->publicId);
        Assert::assertSame('urn:bar', $doc->doctype->systemId);
    }

    public function testHtmlEntitiesWork()
    {
        $doc = Souplette::parseXml(
            '<!DOCTYPE foo PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "ignored"><p>&dollar;666</p>',
            'text/xml'
        );
        Assert::assertSame('$666', $doc->documentElement->textContent);
    }

    public function testVariousNodeTypes()
    {
        $xml = <<<'XML'
        <!-- a comment -->
        <root>
            A text node.
            <?foo A processing instruction ?><![CDATA[
                A CDATA section
            ]]>
            <b attr="value" />
        </root>
        XML;
        $doc = Souplette::parseXml($xml, 'text/xml');
        Assert::assertInstanceOf(Comment::class, $doc->firstChild);
        $root = $doc->documentElement;
        Assert::assertInstanceOf(Text::class, $text = $root->firstChild);
        Assert::assertInstanceOf(ProcessingInstruction::class, $pi = $text->nextSibling);
        Assert::assertInstanceOf(CDATASection::class, $cdata = $pi->nextSibling);
        Assert::assertInstanceOf(Element::class, $b = $cdata->nextElementSibling);
        Assert::assertSame('value', $b->getAttribute('attr'));
    }

    public function testNamespaces()
    {
        $xml = <<<'XML'
        <root xmlns="urn:root" xmlns:x="urn:x">
            <x:child xmlns:y="urn:y" x:attr="xvalue" y:attr="yvalue" />
        </root>
        XML;
        $doc = Souplette::parseXml($xml, 'text/xml');
        $root = $doc->documentElement;
        Assert::assertSame('urn:root', $root->namespaceURI);
        $child = $root->firstElementChild;
        Assert::assertSame('urn:x', $child->namespaceURI);
        Assert::assertSame('xvalue', $child->getAttributeNS('urn:x', 'attr'));
        Assert::assertSame('yvalue', $child->getAttributeNS('urn:y', 'attr'));
    }

    public function testParseFragment()
    {
        $xml = <<<'XML'
        <root xmlns="urn:root" xmlns:x="urn:x">
            <x:child xmlns:y="urn:y" />
        </root>
        XML;
        $frag = <<<'XML'
        <y:grand-child x:attr="xvalue" y:attr="yvalue" />
        XML;
        $doc = Souplette::parseXml($xml, 'text/xml');
        $root = $doc->documentElement;
        $child = $root->firstElementChild;
        [$grandChild] = (new XmlParser())->parseFragment($frag, $child);
        Assert::assertSame('urn:y', $grandChild->namespaceURI);
        Assert::assertSame('xvalue', $grandChild->getAttributeNS('urn:x', 'attr'));
        Assert::assertSame('yvalue', $grandChild->getAttributeNS('urn:y', 'attr'));
    }
}
