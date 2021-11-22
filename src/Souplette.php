<?php declare(strict_types=1);

namespace Souplette;

use Souplette\Html\Dom\Node\HtmlDocument;

final class Souplette
{
    public static function parseHtml(string $html, ?string $encoding = null): HtmlDocument
    {
        $parser = new Html\Parser\Parser();
        return $parser->parse($html, $encoding);
    }
}
