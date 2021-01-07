<?php declare(strict_types=1);

namespace Souplette\Html\Dom;

use DOMDocumentType;
use Souplette\Html\Dom\Node\HtmlDocument;

final class HtmlDomImplementation extends \DOMImplementation
{
    /**
     * @param null $namespace
     * @param null $qualifiedName
     * @param DOMDocumentType|null $doctype
     * @return HtmlDocument
     */
    public function createDocument($namespace = null, $qualifiedName = null, DOMDocumentType $doctype = null): HtmlDocument
    {
        return new HtmlDocument();
    }

    public function createShell(): HtmlDocument
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
