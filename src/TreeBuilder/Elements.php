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

    const SPECIAL = [
        Namespaces::HTML => [
            'address' => true,
            'applet' => true,
            'area' => true,
            'article' => true,
            'aside' => true,
            'base' => true,
            'basefont' => true,
            'bgsound' => true,
            'blockquote' => true,
            'body' => true,
            'br' => true,
            'button' => true,
            'caption' => true,
            'center' => true,
            'col' => true,
            'colgroup' => true,
            'dd' => true,
            'details' => true,
            'dir' => true,
            'div' => true,
            'dl' => true,
            'dt' => true,
            'embed' => true,
            'fieldset' => true,
            'figcaption' => true,
            'figure' => true,
            'footer' => true,
            'form' => true,
            'frame' => true,
            'frameset' => true,
            'h1' => true,
            'h2' => true,
            'h3' => true,
            'h4' => true,
            'h5' => true,
            'h6' => true,
            'head' => true,
            'header' => true,
            'hgroup' => true,
            'hr' => true,
            'html' => true,
            'iframe' => true,
            'img' => true,
            'input' => true,
            'keygen' => true,
            'li' => true,
            'link' => true,
            'listing' => true,
            'main' => true,
            'marquee' => true,
            'menu' => true,
            'meta' => true,
            'nav' => true,
            'noembed' => true,
            'noframes' => true,
            'noscript' => true,
            'object' => true,
            'ol' => true,
            'p' => true,
            'param' => true,
            'plaintext' => true,
            'pre' => true,
            'script' => true,
            'section' => true,
            'select' => true,
            'source' => true,
            'style' => true,
            'summary' => true,
            'table' => true,
            'tbody' => true,
            'td' => true,
            'template' => true,
            'textarea' => true,
            'tfoot' => true,
            'th' => true,
            'thead' => true,
            'title' => true,
            'tr' => true,
            'track' => true,
            'ul' => true,
            'wbr' => true,
            'xmp' => true,
        ],
        Namespaces::MATHML => [
            'mi' => true,
            'mo' => true,
            'mn' => true,
            'ms' => true,
            'mtext' => true,
            'annotation-xml' => true,
        ],
        Namespaces::SVG => [
            'foreignObject' => true,
            'desc' => true,
            'title' => true,
        ],
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

    const SCOPE_BASE = [
        Namespaces::HTML => [
            'applet' => true,
            'caption' => true,
            'html' => true,
            'table' => true,
            'td' => true,
            'th' => true,
            'marquee' => true,
            'object' => true,
            'template' => true,
        ],
        Namespaces::MATHML => [
            'mi' => true,
            'mo' => true,
            'mn' => true,
            'ms' => true,
            'mtext' => true,
            'annotation-xml' => true,
        ],
        Namespaces::SVG => [
            'foreignObject' => true,
            'desc' => true,
            'title' => true,
        ],
    ];

    const SCOPE_LIST_ITEM = [
        Namespaces::HTML => [
            'ol' => true,
            'ul' => true,
        ],
    ];

    const SCOPE_BUTTON = [
        Namespaces::HTML => [
            'button' => true,
        ],
    ];

    const SCOPE_TABLE = [
        Namespaces::HTML => [
            'html' => true,
            'table' => true,
            'template' => true,
        ],
    ];

    const SCOPE_SELECT = [
        Namespaces::HTML => [
            'optgroup' => true,
            'option' => true,
        ],
    ];

    public static function isHtmlIntegrationPoint(string $tagName, string $namespace): bool
    {
        if ($tagName === 'annotation-xml' && $namespace === Namespaces::MATHML) {
            // TODO
        }
        return isset(self::HTHML_INTEGRATION_POINTS[$namespace][$tagName]);
    }

    public static function isMathMlIntegrationPoint(\DOMElement $element)
    {
        return isset(self::MATHML_TEXT_INTEGRATION_POINTS[$element->tagName])
            && $element->namespaceURI === Namespaces::MATHML;
    }
}
