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
        $desc = <<<'TXT'
        1. Check if the prefix of an attribute is replaced with another existing prefix mapped to the same namespace URI
        TXT;
        $doc = DomBuilder::xml()->tag('r', null)
            ->attr('xmlns:xx', 'uri', Namespaces::XMLNS)
            ->attr('p:name', 'v', 'uri')
            ->getDocument();
        yield "#1 {$desc}" => [
            $doc,
            '<r xmlns:xx="uri" xx:name="v"/>',
        ];
        $desc = <<<'TXT'
        2. Check if the prefix of an attribute is replaced with another existing prefix mapped to the same namespace URI
        TXT;
        $doc = DomBuilder::xml()->tag('r', null)
            ->attr('xmlns:xx', 'uri', Namespaces::XMLNS)
            ->tag('b', null)
                ->attr('p:name', 'value', 'uri')
            ->getDocument();
        yield "#1 {$desc}" => [
            $doc,
            '<r xmlns:xx="uri"><b xx:name="value"/></r>',
        ];
    }
}
