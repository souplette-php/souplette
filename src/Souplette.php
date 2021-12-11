<?php declare(strict_types=1);

namespace Souplette;

use Souplette\Dom\Document;
use Souplette\Dom\Exception\DomException;
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
    public static function parseXml(string $xml): XmlDocument
    {
        $parser = new XmlParser();
        return $parser->parse($xml);
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
