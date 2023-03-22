<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\DOM\Nodes\Node;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Souplette\DOM\Namespaces;
use Souplette\DOM\Node;
use Souplette\Tests\DOM\DOMBuilder;

/**
 * Ported from web-platform-tests
 * wpt/dom/nodes/Node-lookupNamespaceURI.html
 */
final class IsDefaultNamespaceTest extends TestCase
{
    #[DataProvider('isDefaultNamespaceProvider')]
    public function testIsDefaultNamespace(Node $node, ?string $namespace, bool $expected): void
    {
        Assert::assertSame($expected, $node->isDefaultNamespace($namespace));
    }

    public static function isDefaultNamespaceProvider(): iterable
    {
        $doc = DOMBuilder::xml()->doctype('html')
            ->tag('root', null)
                ->tag('prefix:elem', 'fooNamespace')->close()
                ->tag('prefix:elem', 'fooNamespace')
                    ->attr('xmlns', 'bazURI', Namespaces::XMLNS)
                    ->prefix('bar', 'barURI')
                    ->comment('comment')
                    ->tag('childElem', 'childNamespace')
            ->getDocument();

        $frag = $doc->createDocumentFragment();
        yield 'DocumentFragment is in default namespace, prefix null' => [$frag, null, true];
        yield 'DocumentFragment is in default namespace, prefix ""' => [$frag, '', true];
        yield 'DocumentFragment is in default namespace, prefix "foo"' => [$frag, 'foo', false];
        yield 'DocumentFragment is in default namespace, prefix "xmlns"' => [$frag, 'xmlns', false];

        $doctype = $doc->doctype;
        yield 'DocumentType is in default namespace, prefix null' => [$doctype, null, true];
        yield 'DocumentType is in default namespace, prefix ""' => [$doctype, '', true];
        yield 'DocumentType is in default namespace, prefix "foo"' => [$doctype, 'foo', false];
        yield 'DocumentType is in default namespace, prefix "xmlns"' => [$doctype, 'xmlns', false];

        $foo = $doc->documentElement->firstChild;
        yield 'Empty namespace is not default, prefix null' => [$foo, null, true];
        yield 'Empty namespace is not default, prefix ""' => [$foo, '', true];
        yield 'fooNamespace is not default' => [$foo, 'fooNamespace', false];
        yield 'xmlns namespace is not default' => [$foo, Namespaces::XMLNS, false];

        $foo = $foo->nextSibling;
        yield 'Empty namespace is not default on fooElem, prefix null' => [$foo, null, false];
        yield 'Empty namespace is not default on fooElem, prefix ""' => [$foo, '', false];
        yield 'bar namespace is not default' => [$foo, 'barURI', false];
        yield 'baz namespace is default' => [$foo, 'bazURI', true];

        $comment = $foo->firstChild;
        yield 'For comment, empty namespace is not default, prefix null' => [$comment, null, false];
        yield 'For comment, empty namespace is not default, prefix ""' => [$comment, '', false];
        yield 'For comment, fooNamespace is not default' => [$comment, 'fooNamespace', false];
        yield 'For comment, xmlns namespace is not default' => [$comment, Namespaces::XMLNS, false];
        yield 'For comment, inherited bar namespace is not default' => [$comment, 'barURI', false];
        yield 'For comment, inherited baz namespace is default' => [$comment, 'bazURI', true];

        $child = $comment->nextSibling;
        yield 'Empty namespace is not default for child, prefix null' => [$child, null, false];
        yield 'Empty namespace is not default for child, prefix ""' => [$child, '', false];
        yield 'fooNamespace is not default for child' => [$child, 'fooNamespace', false];
        yield 'xmlns namespace is not default for child' => [$child, Namespaces::XMLNS, false];
        yield 'bar namespace is not default for child' => [$child, 'barURI', false];
        yield 'baz namespace is default for child' => [$child, 'bazURI', false];
        yield 'childNamespace is default for child' => [$child, 'childNamespace', true];

        $doc = DOMBuilder::html()
            ->tag('html')
                ->attr('xmlns', 'bazURI', Namespaces::XMLNS)
                ->prefix('bar', 'barURI')
            ->close()
            ->getDocument();
        yield 'For document, empty namespace is not default, prefix null' => [$doc, null, false];
        yield 'For document, empty namespace is not default, prefix ""' => [$doc, '', false];
        yield 'For document, fooNamespace is not default' => [$doc, 'fooNamespace', false];
        yield 'For document, xmlns namespace is not default' => [$doc, Namespaces::XMLNS, false];
        yield 'For document, bar namespace is not default' => [$doc, 'barURI', false];
        yield 'For document, baz namespace is not default' => [$doc, 'bazURI', false];
        yield 'For document, xhtml namespace is default' => [$doc, Namespaces::HTML, true];
    }
}
