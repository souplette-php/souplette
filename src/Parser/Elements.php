<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Parser;

final class Elements
{
    const CDATA_ELEMENTS = [
        'textarea' => true,
        'title' => true,
    ];
    const RCDATA_ELEMENTS = [
        'iframe' => true,
        'noembed' => true,
        'noframes' => true,
        'noscript' => true,
        'script' => true,
        'style' => true,
        'xmp' => true,
    ];
    const VOID_ELEMENTS = [
        'area' => true,
        'base' => true,
        'br' => true,
        'col' => true,
        'command' => true,
        'embed' => true,
        'event-source' => true,
        'hr' => true,
        'img' => true,
        'input' => true,
        'link' => true,
        'meta' => true,
        'param' => true,
        'source' => true,
        'track' => true
    ];
    const HEADING_ELEMENTS = [
        'h1' => true,
        'h2' => true,
        'h3' => true,
        'h4' => true,
        'h5' => true,
        'h6' => true,
    ];
    const TABLE_INSERT_MODE_ELEMENTS = [
        'table' => true,
        'tbody' => true,
        'tfoot' => true,
        'thead' => true,
        'tr' => true,
    ];
}
