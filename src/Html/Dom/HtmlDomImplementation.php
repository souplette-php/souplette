<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom;

use DOMDocumentType;

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
}
