<?php declare(strict_types=1);

namespace Souplette\Dom;

final class Namespaces
{
    const HTML = "http://www.w3.org/1999/xhtml";
    const MATHML = "http://www.w3.org/1998/Math/MathML";
    const SVG = "http://www.w3.org/2000/svg";
    const XLINK = "http://www.w3.org/1999/xlink";
    const XML = "http://www.w3.org/XML/1998/namespace";
    const XMLNS = "http://www.w3.org/2000/xmlns/";

    const TO_PREFIX = [
        self::HTML => 'html',
        self::MATHML => 'math',
        self::SVG => 'svg',
        self::XLINK => 'xlink',
        self::XML => 'xml',
        self::XMLNS => 'xmlns',
    ];

    const FROM_PREFIX = [
        'html' => self::HTML,
        'math' => self::MATHML,
        'svg' => self::SVG,
        'xlink' => self::XLINK,
        'xml' => self::XML,
        'xmlns' => self::XMLNS,
    ];
}
