<?php declare(strict_types=1);

namespace Souplette;

use Souplette\Dom\Document;

final class Souplette
{
    public static function parseHtml(string $html, ?string $encoding = null): Document
    {
        $parser = new Html\Parser();
        return $parser->parse($html, $encoding);
    }
}
