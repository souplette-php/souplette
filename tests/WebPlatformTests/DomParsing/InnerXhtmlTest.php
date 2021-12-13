<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\DomParsing;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\Element;
use Souplette\Dom\Exception\InvalidStateError;
use Souplette\Dom\Implementation;
use Souplette\Dom\Namespaces;
use Souplette\Tests\Dom\DomBuilder;

/**
 * Ported from web-platform-tests
 * wpt/domparsing/innerhtml-01.xhtml
 * wpt/domparsing/innerhtml-03.xhtml
 */
final class InnerXhtmlTest extends TestCase
{
    public function testItThrowsOnInvalidCharactersInTextContent()
    {
        $this->expectException(InvalidStateError::class);
        $doc = (new Implementation)->createDocument(Namespaces::HTML, 'html');
        $doc->documentElement->textContent = "\f";
        $html = $doc->documentElement->getInnerHTML();
    }

    public function testItThrowsOnInvalidCharactersInLocalName()
    {
        $doc = (new Implementation)->createDocument(Namespaces::HTML, 'html');
        $doc->documentElement->appendChild($doc->createElement('test:test'));
        $this->expectException(InvalidStateError::class);
        $html = $doc->documentElement->getInnerHTML();
    }

    /**
     * @dataProvider xhtmlSerializationProvider
     */
    public function testXhtmlSerialization(Element $element, string $expected)
    {
        Assert::assertSame($expected, $element->innerHTML);
    }

    public function xhtmlSerializationProvider(): iterable
    {
        $doc = DomBuilder::xml()->tag('html')
            ->tag('xmp')
                ->tag('span')->text('<')
            ->getDocument();
        yield [$doc->documentElement, '<xmp xmlns="http://www.w3.org/1999/xhtml"><span>&lt;</span></xmp>'];

        $doc = DomBuilder::xml()->tag('html')
            ->tag('br')
            ->getDocument();
        yield [$doc->documentElement, '<br xmlns="http://www.w3.org/1999/xhtml" />'];

        $doc = DomBuilder::xml()->tag('html')
            ->tag('html:br')
            ->getDocument();
        yield [$doc->documentElement, '<html:br xmlns:html="http://www.w3.org/1999/xhtml" />'];

        $doc = DomBuilder::xml()->tag('html')
            ->text('<>&\'"')
            ->getDocument();
        yield [$doc->documentElement, '&lt;&gt;&amp;\'"'];

        $doc = DomBuilder::xml()->tag('html')
            ->text('&lt;&gt;&quot;&apos;&amp;')
            ->getDocument();
        yield [$doc->documentElement, '&amp;lt;&amp;gt;&amp;quot;&amp;apos;&amp;amp;'];

        $doc = DomBuilder::xml()->tag('html')
            ->text("à×•…\u{00A0}")
            ->getDocument();
        yield [$doc->documentElement, "à×•…\u{00A0}"];
    }
}
