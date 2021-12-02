<?php declare(strict_types=1);

namespace Souplette\Dom\Node;

final class Implementation
{
    public function createDocumentType(string $name, string $publicId = '',  string $systemId = ''): DocumentType
    {
        return new DocumentType($name, $publicId, $systemId);
    }

    public function createDocument(): Document
    {
        $doc = new Document('html');
        //$doc->implementation = $this;
        return $doc;
    }

    public function createHTMLDocument(string $title = ''): Document
    {
        $doc = new Document('html');
        $html = $doc->createElement('html');
        $head = $doc->createElement('head');
        $titleNode = $doc->createElement('title');
        $titleNode->textContent = 'title';
        $head->appendChild($titleNode);
        $body = $doc->createElement('body');
        $doc->appendChild($html);
        $html->append($head, $body);

        return $doc;
    }
}
