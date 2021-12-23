<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\DOM\Nodes\Node;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\DOM\Namespaces;
use Souplette\DOM\Node;
use Souplette\Tests\DOM\DOMBuilder;

/**
 * Ported from web-platform-tests
 * wpt/dom/nodes/Node-lookupNamespaceURI.html
 */
final class LookupNamespaceURITest extends TestCase
{
    /**
     * @dataProvider lookupNamespaceURIProvider
     */
    public function testLookupNamespaceURI(Node $node, ?string $prefix, ?string $expected): void
    {
        Assert::assertSame($expected, $node->lookupNamespaceURI($prefix));
    }

    public function lookupNamespaceURIProvider(): iterable
    {
        $doc = DOMBuilder::xml()->doctype('html')
            ->tag('root', null)
                ->tag('prefix:elem', 'fooNamespace')->close()
                ->tag('prefix:elem', 'fooNamespace')
                    ->attr('xmlns', 'bazURI', Namespaces::XMLNS)
                    ->prefix('bar', 'barURI')
                    ->comment('comment')
                    ->tag('childElem', 'childNamespace')
                ->close()
            ->getDocument();
        $frag = $doc->createDocumentFragment();

        yield 'DocumentFragment should have null namespace, prefix null' => [$frag, null, null];
        yield 'DocumentFragment should have null namespace, prefix ""' => [$frag, '', null];
        yield 'DocumentFragment should have null namespace, prefix "foo"' => [$frag, 'foo', null];
        yield 'DocumentFragment should have null namespace, prefix "xmlns"' => [$frag, 'xmlns', null];

        $doctype = $doc->doctype;
        yield 'DocumentType should have null namespace, prefix null' => [$doctype, null, null];
        yield 'DocumentType should have null namespace, prefix ""' => [$doctype, '', null];
        yield 'DocumentType should have null namespace, prefix "foo"' => [$doctype, 'foo', null];
        yield 'DocumentType should have null namespace, prefix "xmlns"' => [$doctype, 'xmlns', null];

        $foo = $doc->documentElement->firstChild;
        yield 'Element should have null namespace, prefix null' => [$foo, null, null];
        yield 'Element should have null namespace, prefix ""' => [$foo, '', null];
        yield 'Element should not have namespace matching prefix with namespaceURI value' => [$foo, 'fooNamespace', null];
        yield 'Element should not have XMLNS namespace' => [$foo, 'xmlns', null];
        yield 'Element has namespace URI matching prefix' => [$foo, 'prefix', 'fooNamespace'];

        $foo = $foo->nextSibling;
        yield 'Element should have baz namespace, prefix null' => [$foo, null, 'bazURI'];
        yield 'Element should have baz namespace, prefix ""' => [$foo, '', 'bazURI'];
        yield 'Element does not have namespace with xlmns prefix' => [$foo, 'xmlns', null];
        yield 'Element has bar namespace' => [$foo, 'bar', 'barURI'];

        $comment = $foo->firstChild;
        yield 'Comment should inherit baz namespace' => [$comment, null, 'bazURI'];
        yield 'Comment should inherit  baz namespace' => [$comment, '', 'bazURI'];
        yield 'Comment should inherit namespace URI matching prefix' => [$comment, 'prefix', 'fooNamespace'];
        yield 'Comment should inherit bar namespace' => [$comment, 'bar', 'barURI'];

        $child = $comment->nextSibling;
        yield 'Child element should inherit baz namespace' => [$child, null, 'childNamespace'];
        yield 'Child element should have null namespace' => [$child, '', 'childNamespace'];
        yield 'Child element should not have XMLNS namespace' => [$child, 'xmlns', null];
        yield 'Child element has namespace URI matching prefix' => [$child, 'prefix', 'fooNamespace'];

        $doc = DOMBuilder::html()
            ->tag('html')
                ->attr('xmlns', 'bazURI', Namespaces::XMLNS)
                ->prefix('bar', 'barURI')
            ->close()
            ->comment('comment')
            ->getDocument();
        yield 'Document should have xhtml namespace, prefix null' => [$doc, null, Namespaces::HTML];
        yield 'Document should have xhtml namespace, prefix ""' => [$doc, '', Namespaces::HTML];
        yield 'Document has no namespace URI matching prefix' => [$doc, 'prefix', null];
        yield 'Document has bar namespace' => [$doc, 'bar', 'barURI'];

        $comment = $doc->lastChild;
        yield 'Comment does not have bar namespace' => [$comment, 'bar', null];
    }
}
