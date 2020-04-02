<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom;

use DOMDocumentType;
use JoliPotage\Html\Dom\Node\HtmlDocument;

final class HtmlDomImplementation extends \DOMImplementation
{
    /**
     * @param null $namespaceURI
     * @param null $qualifiedName
     * @param DOMDocumentType|null $doctype
     * @return HtmlDocument
     */
    public function createDocument($namespaceURI = null, $qualifiedName = null, DOMDocumentType $doctype = null)
    {
        return new HtmlDocument();
    }

    public function createShell(): HtmlDocument
    {
        $doc = $this->createDocument();
        $doc->appendChild($this->createDocumentType('html'));
        $html = $doc->createElement('html');
        $head = $html->appendChild($doc->createElement('head'));
        $meta = $head->appendChild($doc->createElement('meta'));
        $meta->setAttribute('charset', 'UTF-8');
        $html->appendChild($doc->createElement('body'));
        $doc->appendChild($html);

        return $doc;
    }
}
