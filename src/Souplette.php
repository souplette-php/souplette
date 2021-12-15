<?php declare(strict_types=1);

namespace Souplette;

use Souplette\DOM\Document;
use Souplette\DOM\Exception\DOMException;
use Souplette\DOM\Exception\NotSupportedError;
use Souplette\DOM\XMLDocument;
use Souplette\HTML\HTMLParser;
use Souplette\HTML\HTMLSerializer;
use Souplette\XML\XMLParser;
use Souplette\XML\XMLSerializer;

final class Souplette
{
    public static function parseHTML(string $html, ?string $encoding = null): Document
    {
        $parser = new HTMLParser();
        return $parser->parse($html, $encoding);
    }

    /**
     * @throws DOMException
     */
    public static function parseXML(string $markup, string $contentType): XMLDocument
    {
        $contentType = $contentType ?: 'application/xml';
        if ($contentType === 'text/html') {
            throw new NotSupportedError(sprintf(
                '%s cannot parse "text/html" documents. Please use %s::parseHTML() instead.',
                __METHOD__,
                self::class,
            ));
        }
        $parser = new XMLParser();
        $document = $parser->parse($markup);
        $document->_contentType = $contentType;
        return $document;
    }

    public static function serializeDocument(Document $document): string
    {
        if ($document instanceof XMLDocument) {
            $serializer = new XMLSerializer();
            return $serializer->serialize($document);
        }
        $serializer = new HTMLSerializer();
        return $serializer->serialize($document);
    }
}
