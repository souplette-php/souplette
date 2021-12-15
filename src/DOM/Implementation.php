<?php declare(strict_types=1);

namespace Souplette\DOM;

use Souplette\DOM\Internal\NodeFlags;

final class Implementation
{
    public function createDocumentType(string $name, string $publicId = '',  string $systemId = ''): DocumentType
    {
        return new DocumentType($name, $publicId, $systemId);
    }

    /**
     * https://dom.spec.whatwg.org/#dom-domimplementation-createdocument
     */
    public function createDocument(?string $namespace, ?string $qualifiedName, ?DocumentType $doctype = null): XMLDocument
    {
        $doc = new XMLDocument();
        $doc->_implementation = $this;
        if ($doctype) {
            $doc->parserInsertBefore($doctype, null);
        }
        if ($qualifiedName) {
            $element = $doc->createElementNS($namespace, $qualifiedName);
            $doc->parserInsertBefore($element, null);
        }
        $doc->_contentType = match ($namespace) {
            Namespaces::HTML => 'application/xhtml+xml',
            Namespaces::SVG => 'image/svg+xml',
            default => 'application/xml',
        };
        $doc->_flags |= match ($namespace) {
            Namespaces::HTML => NodeFlags::NS_TYPE_HTML,
            Namespaces::SVG => NodeFlags::NS_TYPE_SVG,
            Namespaces::MATHML => NodeFlags::NS_TYPE_MATHTML,
            default => NodeFlags::NS_TYPE_OTHER,
        };
        return $doc;
    }

    /**
     * https://dom.spec.whatwg.org/#dom-domimplementation-createhtmldocument
     */
    public function createHTMLDocument(string $title = ''): Document
    {
        $doc = new Document();
        $doc->_implementation = $this;
        $doc->_contentType = 'text/html';
        $doc->parserInsertBefore($this->createDocumentType('html'), null);
        $doc->parserInsertBefore($html = $doc->createElement('html'), null);
        $html->parserInsertBefore($head = $doc->createElement('head'), null);
        if ($title) {
            $titleNode = $doc->createElement('title');
            $titleNode->parserInsertBefore($doc->createTextNode($title), null);
            $head->parserInsertBefore($titleNode, null);
        }
        $html->parserInsertBefore($doc->createElement('body'), null);

        return $doc;
    }

    /**
     * @deprecated
     * @codeCoverageIgnore
     */
    public function hasFeature(): bool
    {
        return true;
    }
}
