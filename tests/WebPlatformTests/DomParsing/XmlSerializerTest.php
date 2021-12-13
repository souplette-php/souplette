<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\DomParsing;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\Namespaces;
use Souplette\Dom\Node;
use Souplette\Dom\XmlDocument;
use Souplette\Tests\Dom\DomBuilder;
use Souplette\Xml\XmlParser;
use Souplette\Xml\XmlSerializer;

final class XmlSerializerTest extends TestCase
{
    private static function createDefaultDocument(): XmlDocument
    {
        return DomBuilder::xml()->tag('root', null)
            ->tag('child1', null)->text('value1')
            ->getDocument();
    }

    private static function parseXml(string $xml): XmlDocument
    {
        return (new XmlParser())->parse($xml);
    }

    /**
     * @dataProvider specOutliersProvider
     */
    public function testSpecOutliers(Node $node, string $expected)
    {
        $serializer = new XmlSerializer();
        Assert::assertSame($expected, $serializer->serialize($node));
    }

    public function specOutliersProvider(): iterable
    {
        // Following test cases are changed because of issues in the spec
        // https://github.com/w3c/DOM-Parsing/issues/48
        $doc = DomBuilder::xml()->tag('root', null)
            ->tag('child', null)->attr('xmlns', '', Namespaces::XMLNS)
            ->getDocument();
        yield 'redundant xmlns="..." is dropped (#1)' => [
            $doc,
            //'<root><child/></root>',
            '<root><child xmlns=""/></root>',
        ];
        // https://github.com/w3c/DOM-Parsing/issues/48
        $doc = DomBuilder::xml()->tag('root', null)->attr('xmlns', '', Namespaces::XMLNS)
            ->tag('child', null)->attr('xmlns', '', Namespaces::XMLNS)
            ->getDocument();
        yield 'redundant xmlns="..." is dropped (#2)' => [
            $doc,
            //'<root><child/></root>',
            '<root xmlns=""><child xmlns=""/></root>',
        ];
        // https://github.com/w3c/DOM-Parsing/issues/48
        $doc = DomBuilder::xml()->tag('root', 'u1')->attr('xmlns', 'u1', Namespaces::XMLNS)
            ->tag('child', 'u1')->attr('xmlns', 'u1', Namespaces::XMLNS)
            ->getDocument();
        yield 'redundant xmlns="..." is dropped (#3)' => [
            $doc,
            //'<root xmlns="u1"><child/></root>',
            '<root xmlns="u1"><child xmlns="u1"/></root>',
        ];
        // https://github.com/w3c/DOM-Parsing/issues/45
        $desc = <<<TXT
        Check if an attribute with namespace and no prefix is serialized with the nearest-declared prefix
        even if the prefix is assigned to another namespace.
        TXT;
        $doc = DomBuilder::xml()
            ->tag('el1', null)
                ->attr('xmlns:p', 'u1', Namespaces::XMLNS)
                ->attr('xmlns:q', 'u1', Namespaces::XMLNS)
            ->tag('el2', null)
                ->attr('xmlns:q', 'u2', Namespaces::XMLNS)
                ->attr('name', 'v', 'u1')
            ->getDocument();
        yield $desc => [
            $doc,
            //'<el1 xmlns:p="u1" xmlns:q="u1"><el2 xmlns:q="u2" q:name="v"/></el1>',
            '<el1 xmlns:p="u1" xmlns:q="u1"><el2 xmlns:q="u2" p:name="v"/></el1>',
        ];
        // https://github.com/w3c/DOM-Parsing/issues/29
        $desc = <<<'TXT'
        Check if the prefix of an attribute is NOT preserved in a case where neither its prefix nor its namespace URI is not already used
        TXT;
        $doc = DomBuilder::xml()->tag('r', null)
            ->attr('xmlns:xx', 'uri', Namespaces::XMLNS)
            ->attr('p:name', 'value', 'uri2')
            ->getDocument();
        yield "#1 {$desc}" => [
            $doc,
            //'<r xmlns:xx="uri" xmlns:ns1="uri2" ns1:name="value"/>',
            '<r xmlns:xx="uri" xmlns:p="uri2" p:name="value"/>',
        ];
        // https://github.com/w3c/DOM-Parsing/issues/52
        $desc = <<<'TXT'
        Check if start tag serialization does NOT apply the default namespace if its namespace is declared in an ancestor.
        TXT;
        $doc = DomBuilder::xml()->tag('root', null)
            ->attr('xmlns:x', 'uri1', Namespaces::XMLNS)
            ->tag('table', 'uri1')
                ->attr('xmlns', 'uri1', Namespaces::XMLNS)
            ->getDocument();
        yield $desc => [
            $doc,
            //'<root xmlns:x="uri1"><x:table xmlns="uri1"/></root>',
            '<root xmlns:x="uri1"><table xmlns="uri1"/></root>',
        ];
        // https://github.com/w3c/DOM-Parsing/issues/44
        $desc = <<<'TXT'
        Check if "ns1" is generated even if the element already has xmlns:ns1.
        TXT;
        $doc = DomBuilder::xml()->tag('root', null)
            ->attr('xmlns:ns2', 'uri2', Namespaces::XMLNS)
            ->tag('child', null)
                ->attr('xmlns:ns1', 'uri1', Namespaces::XMLNS)
                ->attr('attr1', 'value1', 'uri3')
            ->getDocument();
        yield $desc => [
            $doc,
            //'<root xmlns:ns2="uri2"><child xmlns:ns1="uri1" xmlns:ns1="uri3" ns1:attr1="value1"/></root>',
            '<root xmlns:ns2="uri2"><child xmlns:ns1="uri1" xmlns:ns3="uri3" ns3:attr1="value1"/></root>',
        ];
    }

    /**
     * @dataProvider serializeProvider
     */
    public function testSerialize(Node $node, string $expected)
    {
        $serializer = new XmlSerializer();
        Assert::assertSame($expected, $serializer->serialize($node));
    }

    public function serializeProvider(): iterable
    {
        $doc = self::createDefaultDocument();
        yield 'it can serialize a simple document.' => [$doc, '<root><child1>value1</child1></root>'];
        //
        $doc = self::createDefaultDocument();
        $root = $doc->documentElement;
        $element = $doc->createElementNS('urn:foo', 'another');
        $child1 = $root->firstChild;
        $root->replaceChild($element, $child1);
        $element->appendChild($child1);
        yield 'The default namespace should be correctly reset.' => [
            $root,
            '<root><another xmlns="urn:foo"><child1 xmlns="">value1</child1></another></root>'
        ];
        //
        $doc = DomBuilder::xml()->tag('root', 'urn:bar')
            ->tag('outer', null)->attr('xmlns', '', Namespaces::XMLNS)
                ->tag('inner', null)->text('value1')
            ->getDocument();
        yield 'no redundant empty namespace declaration' => [
            $doc->documentElement,
            '<root xmlns="urn:bar"><outer xmlns=""><inner>value1</inner></outer></root>'
        ];
        //
        $doc = DomBuilder::xml()->tag('root', 'uri1')
            ->tag('child', null)
                ->attr('xmlns', 'FAIL1', Namespaces::XMLNS)
            ->close()
            ->tag('child2', 'uri2')
                ->attr('xmlns', 'FAIL2', Namespaces::XMLNS)
            ->close()
            ->tag('child3', 'uri1')
                ->attr('xmlns', 'FAIL3', Namespaces::XMLNS)
            ->close()
            ->tag('child4', 'uri4')
                ->attr('xmlns', 'uri4', Namespaces::XMLNS)
            ->close()
            ->tag('child5', null)
                ->attr('xmlns', '', Namespaces::XMLNS)
            ->close()
            ->getDocument();
        yield 'inconsistent xmlns="..." is dropped' => [
            $doc,
            '<root xmlns="uri1"><child xmlns=""/><child2 xmlns="uri2"/><child3/><child4 xmlns="uri4"/><child5 xmlns=""/></root>',
        ];
        //
        $doc = DomBuilder::xml()->tag('r', null)
            ->attr('xmlns:xx', 'uri', Namespaces::XMLNS)
            ->attr('name', 'v', 'uri')
            ->getDocument();
        yield '#1 an attribute with namespace and no prefix is serialized with the nearest-declared prefix' => [
            $doc,
            '<r xmlns:xx="uri" xx:name="v"/>',
        ];
        $doc = DomBuilder::xml()
            ->tag('r', null)
                ->attr('xmlns:xx', 'uri', Namespaces::XMLNS)
                ->tag('b', null)
                    ->attr('name', 'v', 'uri')
            ->getDocument();
        yield '#2 an attribute with namespace and no prefix is serialized with the nearest-declared prefix' => [
            $doc,
            '<r xmlns:xx="uri"><b xx:name="v"/></r>',
        ];
        $doc = DomBuilder::xml()
            ->tag('r', null)
                ->attr('xmlns:x0', 'uri', Namespaces::XMLNS)
                ->attr('xmlns:x2', 'uri', Namespaces::XMLNS)
            ->tag('b', null)
                ->attr('xmlns:x1', 'uri', Namespaces::XMLNS)
                ->attr('name', 'v', 'uri')
            ->getDocument();
        yield '#3 an attribute with namespace and no prefix is serialized with the nearest-declared prefix' => [
            $doc,
            '<r xmlns:x0="uri" xmlns:x2="uri"><b xmlns:x1="uri" x1:name="v"/></r>',
        ];
        //
        $desc = <<<'TXT'
        Check if the prefix of an attribute is replaced with another existing prefix mapped to the same namespace URI
        TXT;
        $doc = DomBuilder::xml()->tag('r', null)
            ->attr('xmlns:xx', 'uri', Namespaces::XMLNS)
            ->attr('p:name', 'v', 'uri')
            ->getDocument();
        yield "#1 {$desc}" => [
            $doc,
            '<r xmlns:xx="uri" xx:name="v"/>',
        ];
        $doc = DomBuilder::xml()->tag('r', null)
            ->attr('xmlns:xx', 'uri', Namespaces::XMLNS)
            ->tag('b', null)
                ->attr('p:name', 'value', 'uri')
            ->getDocument();
        yield "#2 {$desc}" => [
            $doc,
            '<r xmlns:xx="uri"><b xx:name="value"/></r>',
        ];
        //
        $desc = <<<'TXT'
        Check if the prefix of an attribute is replaced with a generated one in a case where the prefix is already mapped to a different namespace URI
        TXT;
        $doc = DomBuilder::xml()->tag('r', null)
            ->attr('xmlns:xx', 'uri', Namespaces::XMLNS)
            ->attr('xx:name', 'value', 'uri2')
            ->getDocument();
        yield $desc => [
            $doc,
            '<r xmlns:xx="uri" xmlns:ns1="uri2" ns1:name="value"/>',
        ];
        //
        $desc = <<<'TXT'
        check XMLSerializer.serializeToString escapes attribute values for roundtripping
        TXT;
        $doc = DomBuilder::xml()->tag('root', null)
            ->attr('attr', "\t")
            ->getDocument();
        yield "#1 {$desc}" => [
            $doc,
            '<root attr="&#x9;"/>',
        ];
        $doc = DomBuilder::xml()->tag('root', null)
            ->attr('attr', "\n")
            ->getDocument();
        yield "#2 {$desc}" => [
            $doc,
            '<root attr="&#xA;"/>',
        ];
        $doc = DomBuilder::xml()->tag('root', null)
            ->attr('attr', "\r")
            ->getDocument();
        yield "#3 {$desc}" => [
            $doc,
            '<root attr="&#xD;"/>',
        ];
        //
        $desc = <<<'TXT'
        Check if attribute serialization takes into account of following xmlns:* attributes
        TXT;
        $doc = DomBuilder::xml()->tag('root', null)
            ->attr('p:foobar', 'value1', 'uri1')
            ->attr('xmlns:p', 'uri2', Namespaces::XMLNS)
            ->getDocument();
        yield $desc => [
            $doc,
            '<root xmlns:ns1="uri1" ns1:foobar="value1" xmlns:p="uri2"/>',
        ];
        //
        $desc = <<<'TXT'
        Check if attribute serialization takes into account of the same prefix declared in an ancestor element
        TXT;
        $doc = DomBuilder::xml()->tag('root', null)
            ->attr('xmlns:p', 'uri1', Namespaces::XMLNS)
            ->tag('child', null)
                ->attr('p:foobar', 'v', 'uri2')
            ->getDocument();
        yield $desc => [
            $doc,
            '<root xmlns:p="uri1"><child xmlns:ns1="uri2" ns1:foobar="v"/></root>',
        ];
        //
        $desc = <<<'TXT'
        Check if start tag serialization drops element prefix if the namespace is same as inherited default namespace.
        TXT;
        $doc = DomBuilder::xml()->tag('root', null)
            ->tag('child', null)
            ->getDocument();
        yield "#1 {$desc}" => [
            $doc,
            '<root><child/></root>',
        ];
        $doc = DomBuilder::xml()->tag('root', 'u1')
            ->tag('p:child', 'u1')
                ->attr('xmlns:p', 'u1', Namespaces::XMLNS)
            ->getDocument();
        yield "#2 {$desc}" => [
            $doc,
            '<root xmlns="u1"><child xmlns:p="u1"/></root>',
        ];
        //
        $desc = <<<'TXT'
        Check if start tag serialization finds an appropriate prefix.
        TXT;
        $doc = DomBuilder::xml()->tag('root', null)
            ->attr('xmlns:p1', 'u1', Namespaces::XMLNS)
            ->tag('child', null)
                ->attr('xmlns:p2', 'u1', Namespaces::XMLNS)
                ->tag('child2', 'u1')
            ->getDocument();
        yield $desc => [
            $doc,
            '<root xmlns:p1="u1"><child xmlns:p2="u1"><p2:child2/></child></root>',
        ];
        //
        $desc = <<<'TXT'
        Check if start tag serialization takes into account of its xmlns:* attributes
        TXT;
        $doc = DomBuilder::xml()->tag('p:root', 'uri1')
            ->attr('xmlns:p', 'uri2', Namespaces::XMLNS)
            ->getDocument();
        yield $desc => [
            $doc,
            '<ns1:root xmlns:ns1="uri1" xmlns:p="uri2"/>',
        ];
        //
        $desc = <<<'TXT'
        Check if start tag serialization applied the original prefix even if it is declared in an ancestor element
        TXT;
        $doc = DomBuilder::xml()->tag('root', null)
            ->attr('xmlns:p', 'uri2', Namespaces::XMLNS)
            ->tag('p:child', 'uri1')
            ->getDocument();
        yield $desc => [
            $doc,
            '<root xmlns:p="uri2"><p:child xmlns:p="uri1"/></root>',
        ];
        //
        $desc = <<<'TXT'
        Check if generated prefixes match to "ns${index}".
        TXT;
        $doc = DomBuilder::xml()->tag('root', null)
            ->tag('child1', null)
                ->attr('attr1', 'value1', 'uri1')
                ->attr('attr2', 'value2', 'uri2')
            ->close()
            ->tag('child2', null)
                ->attr('attr3', 'value3', 'uri3')
            ->getDocument();
        yield $desc => [
            $doc,
            '<root><child1 xmlns:ns1="uri1" ns1:attr1="value1" xmlns:ns2="uri2" ns2:attr2="value2"/><child2 xmlns:ns3="uri3" ns3:attr3="value3"/></root>',
        ];
        //
        $desc = <<<'TXT'
        Check if no special handling for XLink namespace unlike HTML serializer.
        TXT;
        $doc = DomBuilder::xml()->tag('root', null)
            ->attr('href', 'v', Namespaces::XLINK)
            ->getDocument();
        yield "#1 {$desc}" => [
            $doc,
            '<root xmlns:ns1="http://www.w3.org/1999/xlink" ns1:href="v"/>',
        ];
        $doc = DomBuilder::xml()->tag('root', null)
            ->attr('xl:type', 'v', Namespaces::XLINK)
            ->getDocument();
        yield "#2 {$desc}" => [
            $doc,
            '<root xmlns:xl="http://www.w3.org/1999/xlink" xl:type="v"/>',
        ];
        //
        $desc = <<<'TXT'
        Check if document fragment serializes.
        TXT;
        $doc = DomBuilder::html()->getDocument();
        $frag = $doc->createDocumentFragment();
        $frag->appendChild($doc->createElement('div'));
        $frag->appendChild($doc->createElement('span'));
        yield $desc => [
            $frag,
            '<div xmlns="http://www.w3.org/1999/xhtml"></div><span xmlns="http://www.w3.org/1999/xhtml"></span>',
        ];
    }
}
