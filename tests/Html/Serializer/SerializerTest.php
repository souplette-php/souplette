<?php declare(strict_types=1);

namespace Souplette\Tests\Html\Serializer;

use PHPUnit\Framework\TestCase;
use Souplette\Dom\Document;
use Souplette\Dom\Namespaces;
use Souplette\Dom\Node;
use Souplette\Tests\Dom\DomBuilder;

final class SerializerTest extends TestCase
{
    /**
     * @dataProvider doctypeProvider
     */
    public function testDoctype(Document $input, string $expected, ?string $xhtml = null)
    {
        SerializerAssert::assertSerializationEquals($input, $expected, $xhtml);
    }

    public function doctypeProvider(): iterable
    {
        yield 'html doctype' => [
            DomBuilder::html()->doctype('html')->getDocument(),
            '<!DOCTYPE html>',
            '<!DOCTYPE html>',
        ];
        yield 'HTML 4.01 doctype' => [
            DomBuilder::html()
                ->doctype('HTML', '-//W3C//DTD HTML 4.01//EN', 'http://www.w3.org/TR/html4/strict.dtd')
                ->getDocument(),
            '<!DOCTYPE HTML>',
            '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
        ];
        yield 'random doctype' => [
            DomBuilder::html()
                ->doctype('foo', 'bar', 'baz')
                ->getDocument(),
            '<!DOCTYPE foo>',
            '<!DOCTYPE foo PUBLIC "bar" "baz">',
        ];
    }

    /**
     * @dataProvider commentsProvider
     */
    public function testComments(Document $input, string $expected, ?string $xhtml = null)
    {
        SerializerAssert::assertSerializationEquals($input, $expected, $xhtml);
    }

    public function commentsProvider(): iterable
    {
        yield 'simple comment' => [
            DomBuilder::html()->comment('foobar')->getDocument(),
            '<!--foobar-->',
            '<!--foobar-->',
        ];
        yield 'tricky comment' => [
            DomBuilder::html()->comment('foo--bar')->getDocument(),
            '<!--foo--bar-->',
            '<!--foo--bar-->',
        ];
    }

    /**
     * @dataProvider elementsProvider
     */
    public function testElements(Node $input, string $expected, ?string $xhtml = null)
    {
        SerializerAssert::assertSerializationEquals($input, $expected, $xhtml);
    }

    public function elementsProvider(): iterable
    {
        yield '<div>' => [
            DomBuilder::html()->tag('div')->getDocument(),
            '<div></div>',
            '<div xmlns="http://www.w3.org/1999/xhtml"></div>',
        ];
        yield '<input>' => [
            DomBuilder::html()->tag('input')->getDocument(),
            '<input>',
            '<input xmlns="http://www.w3.org/1999/xhtml" />',
        ];
        $doc = new Document('html');
        $doc->appendChild($input = $doc->createElement('input'));
        $input->textContent = 'foo';
        yield '<input>foo</input>' => [
            $doc,
            '<input>',
            '<input xmlns="http://www.w3.org/1999/xhtml">foo</input>',
        ];
        yield 'foreign namespace' => [
            DomBuilder::html()->tag('foo:bar', 'http://example.com')->getDocument(),
            '<foo:bar></foo:bar>',
            '<foo:bar xmlns:foo="http://example.com"/>',
        ];
    }

    /**
     * @dataProvider attributesProvider
     */
    public function testAttributes(Document $input, string $expected, ?string $xhtml = null)
    {
        SerializerAssert::assertSerializationEquals($input, $expected, $xhtml);
    }

    public function attributesProvider(): iterable
    {
        yield 'double quote escaping' => [
            DomBuilder::html()->tag('span')->attr('title', 'foo"bar')->getDocument(),
            '<span title="foo&quot;bar"></span>',
            '<span xmlns="http://www.w3.org/1999/xhtml" title="foo&quot;bar"></span>',
        ];
        yield '& escaping' => [
            DomBuilder::html()->tag('span')->attr('title', 'foo & bar')->getDocument(),
            '<span title="foo &amp; bar"></span>',
            '<span xmlns="http://www.w3.org/1999/xhtml" title="foo &amp; bar"></span>',
        ];
        yield 'non-breaking-space escaping' => [
            DomBuilder::html()->tag('span')->attr('title', "foo \u{00A0} bar")->getDocument(),
            '<span title="foo &nbsp; bar"></span>',
            "<span xmlns=\"http://www.w3.org/1999/xhtml\" title=\"foo \u{A0} bar\"></span>",
        ];
        yield 'non-escaping other characters' => [
            DomBuilder::html()->tag('span')->attr('title', "<a b='c'>")->getDocument(),
            '<span title="<a b=\'c\'>"></span>',
            '<span xmlns="http://www.w3.org/1999/xhtml" title="&lt;a b=\'c\'&gt;"></span>',
        ];
        yield 'xml:lang attribute' => [
            DomBuilder::html()->tag('div')
                ->attr('xml:lang', 'en', Namespaces::XML)
                ->getDocument(),
            '<div xml:lang="en"></div>',
            // wierd, but this is by the spec...
            // see https://github.com/w3c/DOM-Parsing/issues/29
            '<div xmlns="http://www.w3.org/1999/xhtml" xmlns:ns1="http://www.w3.org/XML/1998/namespace" ns1:lang="en"></div>',
        ];
        yield 'xlink:href attribute' => [
            DomBuilder::html()->tag('div')
                ->attr('xlink:href', '#foo', Namespaces::XLINK)
                ->getDocument(),
            '<div xlink:href="#foo"></div>',
            // also see https://github.com/w3c/DOM-Parsing/issues/29
            '<div xmlns="http://www.w3.org/1999/xhtml" xmlns:ns1="http://www.w3.org/1999/xlink" ns1:href="#foo"></div>',
        ];
        yield 'xmlns attributes' => [
            DomBuilder::html()->tag('div')
                ->attr('xmlns:foo', 'http://example.com', Namespaces::XMLNS)
                ->getDocument(),
            '<div xmlns:foo="http://example.com"></div>',
            '<div xmlns="http://www.w3.org/1999/xhtml" xmlns:foo="http://example.com"></div>',
        ];
        yield 'random foreign attributes' => [
            DomBuilder::html()->tag('div')
                ->attr('foo:bar', 'baz', 'http://example.com')
                ->getDocument(),
            '<div foo:bar="baz"></div>',
            // also see https://github.com/w3c/DOM-Parsing/issues/29
            '<div xmlns="http://www.w3.org/1999/xhtml" xmlns:ns1="http://example.com" ns1:bar="baz"></div>',
        ];
    }

    /**
     * @dataProvider characterDataEscapingProvider
     */
    public function testCharacterDataEscaping(Document $input, string $expected, ?string $xhtml = null)
    {
        SerializerAssert::assertSerializationEquals($input, $expected, $xhtml);
    }

    public function characterDataEscapingProvider(): iterable
    {
        yield 'character data' => [
            DomBuilder::html()->tag('span')->text("<foo> & bar\u{00A0}baz")->getDocument(),
            '<span>&lt;foo&gt; &amp; bar&nbsp;baz</span>',
            "<span xmlns=\"http://www.w3.org/1999/xhtml\">&lt;foo&gt; &amp; bar\u{00A0}baz</span>",
        ];
        yield 'rcdata' => [
            DomBuilder::html()->tag('script')->text("<foo> & bar\u{00A0}baz")->getDocument(),
            "<script><foo> & bar\u{A0}baz</script>",
            "<script xmlns=\"http://www.w3.org/1999/xhtml\">&lt;foo&gt; &amp; bar\u{A0}baz</script>",
        ];
    }

    /**
     * @dataProvider booleanAttributesProvider
     */
    public function testBooleanAttributes(Document $input, string $expected, ?string $xhtml = null)
    {
        SerializerAssert::assertSerializationEquals($input, $expected, $xhtml);
    }

    public function booleanAttributesProvider(): iterable
    {
        yield '<div hidden="">' => [
            DomBuilder::html()->tag('div')->attr('hidden')->getDocument(),
            '<div hidden></div>',
            '<div xmlns="http://www.w3.org/1999/xhtml" hidden=""></div>',
        ];
        yield '<div hidden="hidden">' => [
            DomBuilder::html()->tag('div')->attr('hidden', 'hidden')->getDocument(),
            '<div hidden></div>',
            '<div xmlns="http://www.w3.org/1999/xhtml" hidden="hidden"></div>',
        ];
        yield '<input disabled="">' => [
            DomBuilder::html()->tag('input')->attr('disabled')->getDocument(),
            '<input disabled>',
            '<input xmlns="http://www.w3.org/1999/xhtml" disabled="" />',
        ];
        yield '<input disabled="disabled">' => [
            DomBuilder::html()->tag('input')->attr('disabled', 'disabled')->getDocument(),
            '<input disabled>',
            '<input xmlns="http://www.w3.org/1999/xhtml" disabled="disabled" />',
        ];
    }
}
