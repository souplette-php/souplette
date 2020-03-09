<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder;

use ju1ius\HtmlParser\Namespaces;

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

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#closing-elements-that-have-implied-end-tags
     */
    const END_TAG_IMPLIED = [
        'dd' => true,
        'dt' => true,
        'li' => true,
        'optgroup' => true,
        'option' => true,
        'p' => true,
        'rb' => true,
        'rp' => true,
        'rt' => true,
        'rtc' => true,
    ];
    const END_TAG_IMPLIED_THOROUGH = self::END_TAG_IMPLIED + [
        'caption' => true,
        'colgroup' => true,
        'tbody' => true,
        'td' => true,
        'tfoot' => true,
        'th' => true,
        'thead' => true,
        'tr' => true,
    ];

    const MATHML_TEXT_INTEGRATION_POINTS = [
        'mi' => true,
        'mo' => true,
        'mn' => true,
        'ms' => true,
        'mtext' => true,
    ];

    const HTHML_INTEGRATION_POINTS = [
        Namespaces::SVG => [
            'foreignObject' => true,
            'desc' => true,
            'title' => true,
        ],
    ];

    public static function isHtmlIntegrationPoint(string $tagName, string $namespace): bool
    {
        if ($tagName === 'annotation-xml' && $namespace === Namespaces::MATHML) {

        }
        return isset(self::HTHML_INTEGRATION_POINTS[$namespace][$tagName]);
    }

    public static function isMathMlIntegrationPoint(\DOMElement $element)
    {
        return isset(self::MATHML_TEXT_INTEGRATION_POINTS[$element->tagName])
            && $element->namespaceURI === Namespaces::MATHML;
    }
}
