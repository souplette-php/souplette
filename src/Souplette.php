<?php declare(strict_types=1);

namespace Souplette;

use Souplette\Dom\Legacy\Document;

final class Souplette
{
    public static function parseHtml(string $html, ?string $encoding = null): Document
    {
        $parser = new Html\Parser();
        return $parser->parse($html, $encoding);
    }

    public static function serializeDocument(Document $document): string
    {
        $serializer = new Html\Serializer();
        return $serializer->serialize($document);
    }
}
