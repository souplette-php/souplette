<?php declare(strict_types=1);

namespace Souplette\Tests\HTML\Serializer;

use PHPUnit\Framework\TestCase;
use Souplette\DOM\Document;
use Souplette\DOM\Namespaces;
use Souplette\DOM\Node;
use Souplette\Tests\DOM\DOMBuilder;

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
            DOMBuilder::html()->doctype('html')->getDocument(),
            '<!DOCTYPE html>',
            '<!DOCTYPE html>',
        ];
        yield 'HTML 4.01 doctype' => [
            DOMBuilder::html()
                ->doctype('HTML', '-//W3C//DTD HTML 4.01//EN', 'http://www.w3.org/TR/html4/strict.dtd')
                ->getDocument(),
            '<!DOCTYPE HTML>',
            '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
        ];
        yield 'random doctype' => [
            DOMBuilder::html()
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
            DOMBuilder::html()->comment('foobar')->getDocument(),
            '<!--foobar-->',
            '<!--foobar-->',
        ];
        yield 'tricky comment' => [
            DOMBuilder::html()->comment('foo--bar')->getDocument(),
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
            DOMBuilder::html()->tag('div')->getDocument(),
            '<div></div>',
            '<div xmlns="http://www.w3.org/1999/xhtml"></div>',
        ];
        yield '<input>' => [
            DOMBuilder::html()->tag('input')->getDocument(),
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
            DOMBuilder::html()->tag('foo:bar', 'http://example.com')->getDocument(),
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
            DOMBuilder::html()->tag('span')->attr('title', 'foo"bar')->getDocument(),
            '<span title="foo&quot;bar"></span>',
            '<span xmlns="http://www.w3.org/1999/xhtml" title="foo&quot;bar"></span>',
        ];
        yield '& escaping' => [
            DOMBuilder::html()->tag('span')->attr('title', 'foo & bar')->getDocument(),
            '<span title="foo &amp; bar"></span>',
            '<span xmlns="http://www.w3.org/1999/xhtml" title="foo &amp; bar"></span>',
        ];
        yield 'non-breaking-space escaping' => [
            DOMBuilder::html()->tag('span')->attr('title', "foo \u{00A0} bar")->getDocument(),
            '<span title="foo &nbsp; bar"></span>',
            "<span xmlns=\"http://www.w3.org/1999/xhtml\" title=\"foo \u{A0} bar\"></span>",
        ];
        yield 'non-escaping other characters' => [
            DOMBuilder::html()->tag('span')->attr('title', "<a b='c'>")->getDocument(),
            '<span title="<a b=\'c\'>"></span>',
            '<span xmlns="http://www.w3.org/1999/xhtml" title="&lt;a b=\'c\'&gt;"></span>',
        ];
        yield 'xml:lang attribute' => [
            DOMBuilder::html()->tag('div')
                ->attr('xml:lang', 'en', Namespaces::XML)
                ->getDocument(),
            '<div xml:lang="en"></div>',
            '<div xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"></div>',
        ];
        yield 'xlink:href attribute' => [
            DOMBuilder::html()->tag('div')
                ->attr('xlink:href', '#foo', Namespaces::XLINK)
                ->getDocument(),
            '<div xlink:href="#foo"></div>',
            // see https://github.com/w3c/DOM-Parsing/issues/29
            '<div xmlns="http://www.w3.org/1999/xhtml" xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#foo"></div>',
        ];
        yield 'xmlns attributes' => [
            DOMBuilder::html()->tag('div')
                ->attr('xmlns:foo', 'http://example.com', Namespaces::XMLNS)
                ->getDocument(),
            '<div xmlns:foo="http://example.com"></div>',
            '<div xmlns="http://www.w3.org/1999/xhtml" xmlns:foo="http://example.com"></div>',
        ];
        yield 'random foreign attributes' => [
            DOMBuilder::html()->tag('div')
                ->attr('foo:bar', 'baz', 'http://example.com')
                ->getDocument(),
            '<div foo:bar="baz"></div>',
            // also see https://github.com/w3c/DOM-Parsing/issues/29
            '<div xmlns="http://www.w3.org/1999/xhtml" xmlns:foo="http://example.com" foo:bar="baz"></div>',
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
            DOMBuilder::html()->tag('span')->text("<foo> & bar\u{00A0}baz")->getDocument(),
            '<span>&lt;foo&gt; &amp; bar&nbsp;baz</span>',
            "<span xmlns=\"http://www.w3.org/1999/xhtml\">&lt;foo&gt; &amp; bar\u{00A0}baz</span>",
        ];
        yield 'rcdata' => [
            DOMBuilder::html()->tag('script')->text("<foo> & bar\u{00A0}baz")->getDocument(),
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
            DOMBuilder::html()->tag('div')->attr('hidden')->getDocument(),
            '<div hidden></div>',
            '<div xmlns="http://www.w3.org/1999/xhtml" hidden=""></div>',
        ];
        yield '<div hidden="hidden">' => [
            DOMBuilder::html()->tag('div')->attr('hidden', 'hidden')->getDocument(),
            '<div hidden></div>',
            '<div xmlns="http://www.w3.org/1999/xhtml" hidden="hidden"></div>',
        ];
        yield '<input disabled="">' => [
            DOMBuilder::html()->tag('input')->attr('disabled')->getDocument(),
            '<input disabled>',
            '<input xmlns="http://www.w3.org/1999/xhtml" disabled="" />',
        ];
        yield '<input disabled="disabled">' => [
            DOMBuilder::html()->tag('input')->attr('disabled', 'disabled')->getDocument(),
            '<input disabled>',
            '<input xmlns="http://www.w3.org/1999/xhtml" disabled="disabled" />',
        ];
    }
}
