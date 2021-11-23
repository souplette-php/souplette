<?php declare(strict_types=1);

namespace Souplette\Dom;

use DOMDocumentType;

final class HtmlDomImplementation extends \DOMImplementation
{
    public function createDocument($namespace = null, $qualifiedName = null, DOMDocumentType $doctype = null): Document
    {
        return new Document();
    }

    public function createShell(): Document
    {
        $doc = $this->createDocument();
        $doc->appendChild($this->createDocumentType('html'));
        $html = $doc->createElement('html');
        $html->appendChild($head = $doc->createElement('head'));
        $head->appendChild($meta = $doc->createElement('meta'));
        $meta->setAttribute('charset', 'UTF-8');
        $html->appendChild($doc->createElement('body'));
        $doc->appendChild($html);

        return $doc;
    }
}
