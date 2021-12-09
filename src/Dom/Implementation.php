<?php declare(strict_types=1);

namespace Souplette\Dom;

final class Implementation
{
    public function createDocumentType(string $name, string $publicId = '',  string $systemId = ''): DocumentType
    {
        return new DocumentType($name, $publicId, $systemId);
    }

    public function createDocument(): Document
    {
        $doc = new Document('html');
        $doc->_implementation = $this;
        return $doc;
    }

    public function createHTMLDocument(string $title = ''): Document
    {
        $doc = new Document('html');
        $doc->appendChild($this->createDocumentType('html'));
        $doc->appendChild($html = $doc->createElement('html'));
        $html->appendChild($head = $doc->createElement('head'));
        if ($title) {
            $titleNode = $doc->createElement('title');
            $titleNode->textContent = $title;
            $head->appendChild($titleNode);
        }
        $html->appendChild($doc->createElement('body'));

        return $doc;
    }
}
