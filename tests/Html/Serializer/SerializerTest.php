<?php declare(strict_types=1);

namespace Souplette\Tests\Html\Serializer;

use PHPUnit\Framework\TestCase;
use Souplette\Dom\Namespaces;
use Souplette\Tests\Dom\DomBuilder;

final class SerializerTest extends TestCase
{
    /**
     * @dataProvider doctypeProvider
     * @param \DOMDocument $input
     * @param string $expected
     */
    public function testDoctype(\DOMDocument $input, string $expected)
    {
        SerializerAssert::assertSerializationEquals($input, $expected);
    }

    public function doctypeProvider()
    {
        yield 'html doctype' => [
            DomBuilder::create()->doctype('html')->getDocument(),
            '<!DOCTYPE html>',
        ];
        yield 'HTML 4.01 doctype' => [
            DomBuilder::create()
                ->doctype('HTML', '-//W3C//DTD HTML 4.01//EN', 'http://www.w3.org/TR/html4/strict.dtd')
                ->getDocument(),
            '<!DOCTYPE HTML>',
        ];
        yield 'random doctype' => [
            DomBuilder::create()
                ->doctype('foo', 'bar', 'baz')
                ->getDocument(),
            '<!DOCTYPE foo>',
        ];
    }

    /**
     * @dataProvider commentsProvider
     * @param \DOMDocument $input
     * @param string $expected
     */
    public function testComments(\DOMDocument $input, string $expected)
    {
        SerializerAssert::assertSerializationEquals($input, $expected);
    }

    public function commentsProvider()
    {
        yield [
            DomBuilder::create()->comment('foobar')->getDocument(),
            '<!--foobar-->',
        ];
    }

    /**
     * @dataProvider elementsProvider
     * @param \DOMDocument $input
     * @param string $expected
     */
    public function testElements(\DOMDocument $input, string $expected)
    {
        SerializerAssert::assertSerializationEquals($input, $expected);
    }

    public function elementsProvider()
    {
        yield '<div>' => [
            DomBuilder::create()->tag('div')->getDocument(),
            '<div></div>',
        ];
        yield '<input>' => [
            DomBuilder::create()->tag('input')->getDocument(),
            '<input>',
        ];
        yield '<input>foo</input>' => [
            DomBuilder::create()
                ->tag('input')
                ->text('foo')
                ->getDocument(),
            '<input>',
        ];
        yield 'foreign namespace' => [
            DomBuilder::create()->tag('foo:bar', 'http://example.com')->getDocument(),
            '<foo:bar></foo:bar>',
        ];
    }

    /**
     * @dataProvider attributesProvider
     * @param \DOMDocument $input
     * @param string $expected
     */
    public function testAttributes(\DOMDocument $input, string $expected)
    {
        SerializerAssert::assertSerializationEquals($input, $expected);
    }

    public function attributesProvider()
    {
        yield 'double quote escaping' => [
            DomBuilder::create()->tag('span')->attr('title', 'foo"bar')->getDocument(),
            '<span title="foo&quot;bar"></span>',
        ];
        yield '& escaping' => [
            DomBuilder::create()->tag('span')->attr('title', 'foo & bar')->getDocument(),
            '<span title="foo &amp; bar"></span>',
        ];
        yield 'non-breaking-space escaping' => [
            DomBuilder::create()->tag('span')->attr('title', "foo \u{00A0} bar")->getDocument(),
            '<span title="foo &nbsp; bar"></span>',
        ];
        yield 'non-escaping other characters' => [
            DomBuilder::create()->tag('span')->attr('title', "<a b='c'>")->getDocument(),
            '<span title="<a b=\'c\'>"></span>',
        ];
        yield 'xml foreign attributes' => [
            DomBuilder::create()->tag('div')
                ->attr('xml:lang', 'en', Namespaces::XML)
                ->attr('xlink:href', '#foo', Namespaces::XLINK)
                ->getDocument(),
            '<div xml:lang="en" xlink:href="#foo"></div>',
        ];
        // TODO: xmlns attributes
        //yield 'xmlns attributes' => [
        //    DomBuilder::create()->tag('div')
        //        ->attr('xmlns:foo', 'http://example.com', Namespaces::XMLNS)
        //        ->getDocument(),
        //    '<div xmlns:foo="http://example.com"></div>',
        //];
        yield 'random foreign attributes' => [
            DomBuilder::create()->tag('div')
                ->attr('foo:bar', 'baz', 'http://example.com')
                ->getDocument(),
            '<div foo:bar="baz"></div>',
        ];
    }

    /**
     * @dataProvider characterDataEscapingProvider
     * @param \DOMDocument $input
     * @param string $expected
     */
    public function testCharacterDataEscaping(\DOMDocument $input, string $expected)
    {
        SerializerAssert::assertSerializationEquals($input, $expected);
    }

    public function characterDataEscapingProvider()
    {
        yield 'character data' => [
            DomBuilder::create()->tag('span')->text("<foo> & bar\u{00A0}baz")->getDocument(),
            '<span>&lt;foo&gt; &amp; bar&nbsp;baz</span>',
        ];
        yield 'rcdata' => [
            DomBuilder::create()->tag('script')->text("<foo> & bar\u{00A0}baz")->getDocument(),
            "<script><foo> & bar\u{A0}baz</script>",
        ];
    }

    /**
     * @dataProvider booleanAttributesProvider
     * @param \DOMDocument $input
     * @param string $expected
     */
    public function testBooleanAttributes(\DOMDocument $input, string $expected)
    {
        SerializerAssert::assertSerializationEquals($input, $expected);
    }

    public function booleanAttributesProvider()
    {
        yield '<div hidden="">' => [
            DomBuilder::create()->tag('div')->attr('hidden', '')->getDocument(),
            '<div hidden></div>',
        ];
        yield '<div hidden="hidden">' => [
            DomBuilder::create()->tag('div')->attr('hidden', 'hidden')->getDocument(),
            '<div hidden></div>',
        ];
        yield '<input disabled="">' => [
            DomBuilder::create()->tag('input')->attr('disabled', '')->getDocument(),
            '<input disabled>',
        ];
        yield '<input disabled="disabled">' => [
            DomBuilder::create()->tag('input')->attr('disabled', 'disabled')->getDocument(),
            '<input disabled>',
        ];
    }
}
