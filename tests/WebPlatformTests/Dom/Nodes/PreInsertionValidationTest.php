<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\Dom\Nodes;

use PHPUnit\Framework\TestCase;
use Souplette\Dom\Comment;
use Souplette\Dom\DocumentType;
use Souplette\Dom\Exception\HierarchyRequestError;
use Souplette\Dom\Exception\NotFoundError;
use Souplette\Dom\Implementation;
use Souplette\Dom\Text;

final class PreInsertionValidationTest extends TestCase
{
    /**
     * @see wpt/dom/nodes/pre-insertion-validation-hierarchy.js
     * @dataProvider hierarchyRequestErrorProvider
     */
    public function testHierarchyRequestError(callable $insert)
    {
        $this->expectException(HierarchyRequestError::class);
        $insert();
    }

    public function hierarchyRequestErrorProvider(): iterable
    {
        $methods = [
            'appendChild',
            'insertBefore',
            'append',
            'prepend',
        ];
        foreach ($methods as $method) {
            yield from self::createHierarchyRequestErrorProvider($method);
        }
    }

    /**
     * @see wpt/dom/nodes/pre-insertion-validation-notfound.js
     * @dataProvider notFoundErrorProvider
     */
    public function testNotFoundError(callable $insert, string $expected)
    {
        $this->expectException($expected);
        $insert();
    }

    public function notFoundErrorProvider(): iterable
    {
        $methods = [
            'insertBefore',
            'replaceChild',
        ];
        foreach ($methods as $method) {
            yield from self::createNotFoundErrorProvider($method);
        }
    }

    private static function createHierarchyRequestErrorProvider(string $method): iterable
    {
        $implementation = new Implementation();
        $doc = $implementation->createDocument();
        $parent = $doc->createElement('div');
        $child = $doc->createElement('p');
        $parent->appendChild($child);
        // step 2
        yield "insert node inside itself ({$method})" => [
            fn() => $parent->{$method}($parent),
        ];
        yield "insert ancestor in child ({$method})" => [
            fn() => $child->{$method}($parent),
        ];
        // step 4
        $doc2 = $implementation->createDocument();
        yield "insert document in document ({$method})" => [
            fn() => $doc->{$method}($doc2),
        ];
        // Step 5, in case of inserting a text node into a document
        $text = $doc->createTextNode('text');
        yield "insert text node in document ({$method})" => [
            fn() => $doc->{$method}($text),
        ];
        // Step 5, in case of inserting a doctype into a non-document
        $doctype = $implementation->createDocumentType('html');
        yield "insert doctype in non-document ({$method})" => [
            fn() => $parent->{$method}($doctype),
        ];
        // Step 6, in case of DocumentFragment including multiple elements
        $frag = $doc->createDocumentFragment();
        $frag->appendChild($doc->createElement('a'));
        $frag->appendChild($doc->createElement('b'));
        yield "insert fragment with multiple children in document ({$method})" => [
            fn() => $doc->{$method}($frag),
        ];
        // Step 6, in case of DocumentFragment has multiple elements when document already has an element
        $doc2 = $implementation->createDocument();
        $doc2->appendChild($doc2->createElement('html'));
        $frag = $doc->createDocumentFragment();
        $frag->appendChild($doc2->createElement('a'));
        yield "insert fragment in document with a document element ({$method})" => [
            fn() => $doc2->{$method}($frag),
        ];
        // Step 6, in case of an element
        $node = $doc2->createElement('a');
        yield "insert element in document with a document element ({$method})" => [
            fn() => $doc2->{$method}($node),
        ];
        // Step 6, in case of a doctype when document already has another doctype
        $doc2 = $implementation->createDocument();
        $doc2->appendChild($implementation->createDocumentType('html'));
        yield "insert doctype in document with another doctype ({$method})" => [
            fn() => $doc2->{$method}($implementation->createDocumentType('foo')),
        ];
        // Step 6, in case of a doctype when document has an element
        if ($method !== 'prepend') {
            // Skip `.prepend` as this doesn't throw if `child` is an element
            $doc2 = $implementation->createDocument();
            $doc2->appendChild($doc2->createElement('html'));
            yield "insert doctype in document with a document element ({$method})" => [
                fn() => $doc2->{$method}($implementation->createDocumentType('html')),
            ];
        }

        if (method_exists(DocumentType::class, $method)) {
            $node = $implementation->createDocumentType('html');
            yield "calling {$method} on a DocumentType node)" => [
                fn() => $node->{$method}($doc->createTextNode('foo')),
            ];
        }
        if (method_exists(Text::class, $method)) {
            $node = $doc->createTextNode('foo');
            yield "calling {$method} on a Text node)" => [
                fn() => $node->{$method}($doc->createTextNode('bar')),
            ];
        }
        if (method_exists(Comment::class, $method)) {
            $node = $doc->createComment('foo');
            yield "calling {$method} on a Comment node)" => [
                fn() => $node->{$method}($doc->createTextNode('bar')),
            ];
        }
    }

    private static function createNotFoundErrorProvider(string $method): iterable
    {
        $impl = new Implementation();
        $doc = $impl->createDocument();

        // Step 1 happens before step 3.
        // Should check the 'parent' type before checking whether 'child' is a child of 'parent'
        $nonParentNodes = [
            $impl->createDocumentType('html'),
            $doc->createTextNode('text'),
            $doc->createComment('comment'),
            $doc->createProcessingInstruction('foo', 'bar'),
            // TODO: test CDATA sections in XML documents
            //$doc->createCDATASection('cdata'),
        ];
        $child = $doc->createElement('div');
        $node = $doc->createElement('div');
        foreach ($nonParentNodes as $parent) {
            yield [
                fn() => $parent->{$method}($node, $child),
                HierarchyRequestError::class,
            ];
        }
        // Step 2 happens before step 3.
        // Should check that 'node' is not an ancestor of 'parent' before checking whether 'child' is a child of 'parent'
        $parent = $doc->createElement('div');
        $child = $doc->createElement('div');
        $node = $doc->createElement('div');
        $node->appendChild($parent);
        yield [
            fn() => $parent->{$method}($node, $child),
            HierarchyRequestError::class,
        ];
        // Step 3 happens before step 4.
        // Should check whether 'child' is a child of 'parent' before checking
        // whether 'node' is of a type that can have a parent.
        $nonInsertableNodes = [
            $impl->createDocument(),
        ];
        $parent = $doc->createElement('div');
        $child = $doc->createElement('div');
        foreach ($nonInsertableNodes as $node) {
            yield [
                fn() => $parent->{$method}($node, $child),
                NotFoundError::class,
            ];
        }
        // Step 3 happens before step 5.
        // Should check whether 'child' is a child of 'parent' before checking
        // whether 'node' is of a type that can have a parent of the type that 'parent' is.
        $parent = $impl->createDocument();
        $child = $doc->createElement('div');
        $node = $doc->createTextNode('');
        yield [
            fn() => $parent->{$method}($node, $child),
            NotFoundError::class,
        ];
        $nonDocumentParentNodes = [
            $doc->createElement('div'),
            $doc->createDocumentFragment(),
        ];
        $node = $impl->createDocumentType('html');
        foreach ($nonDocumentParentNodes as $parent) {
            yield [
                fn() => $parent->{$method}($node, $child),
                NotFoundError::class,
            ];
        }
        // Step 3 happens before step 6.
        // Should check whether 'child' is a child of 'parent' before checking whether 'node'
        // can be inserted into the document given the kids the document has right now.
        $child = $doc->createElement('div');
        $parent = $impl->createDocument();
        $node = $doc->createDocumentFragment();
        $node->appendChild($doc->createElement('div'));
        $node->appendChild($doc->createElement('div'));
        yield [
            fn() => $parent->{$method}($node, $child),
            NotFoundError::class,
        ];

        $node = $doc->createElement('div');
        $parent->appendChild($doc->createElement('div'));
        yield [
            fn() => $parent->{$method}($node, $child),
            NotFoundError::class,
        ];

        $parent->firstChild->remove();
        $parent->appendChild($impl->createDocumentType('html'));
        $node = $impl->createDocumentType('html');
        yield [
            fn() => $parent->{$method}($node, $child),
            NotFoundError::class,
        ];
    }
}
