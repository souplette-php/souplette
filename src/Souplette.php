<?php declare(strict_types=1);

namespace Souplette;

use Souplette\Dom\Document;
use Souplette\Dom\Exception\DomException;
use Souplette\Dom\Exception\NotSupportedError;
use Souplette\Dom\XmlDocument;
use Souplette\Html\HtmlParser;
use Souplette\Html\HtmlSerializer;
use Souplette\Xml\XmlParser;
use Souplette\Xml\XmlSerializer;

final class Souplette
{
    public static function parseHtml(string $html, ?string $encoding = null): Document
    {
        $parser = new HtmlParser();
        return $parser->parse($html, $encoding);
    }

    /**
     * @throws DomException
     */
    public static function parseXml(string $markup, string $contentType): XmlDocument
    {
        $contentType = $contentType ?: 'application/xml';
        if ($contentType === 'text/html') {
            throw new NotSupportedError(sprintf(
                '%s cannot parse "text/html" documents. Please use %s::parseHtml() instead.',
                __METHOD__,
                self::class,
            ));
        }
        $parser = new XmlParser();
        $document = $parser->parse($markup);
        $document->_contentType = $contentType;
        return $document;
    }

    public static function serializeDocument(Document $document): string
    {
        if ($document instanceof XmlDocument) {
            $serializer = new XmlSerializer();
            return $serializer->serialize($document);
        }
        $serializer = new HtmlSerializer();
        return $serializer->serialize($document);
    }
}
