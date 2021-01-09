<?php declare(strict_types=1);

namespace Souplette\Xml;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#coercing-an-html-dom-into-an-infoset
 */
final class XmlNameEscaper
{
    /**
     * @see https://www.w3.org/TR/xml/#NT-Name
     */
    private const INVALID_NAME_PATTERN = <<<REGEXP
    /
    (?(DEFINE)
        (?<NameStartChar> : | [A-Z] | _ | [a-z] | [\x{C0}-\x{D6}] | [\x{D8}-\x{F6}] | [\x{F8}-\x{2FF}]
            | [\x{370}-\x{37D}] | [\x{37F}-\x{1FFF}] | [\x{200C}-\x{200D}] | [\x{2070}-\x{218F}] | [\x{2C00}-\x{2FEF}]
            | [\x{3001}-\x{D7FF}] | [\x{F900}-\x{FDCF}] | [\x{FDF0}-\x{FFFD}] | [\x{10000}-\x{EFFFF}]
        )
        (?<NameChar> (?&NameStartChar) | - | \. | [0-9] | \x{B7} | [\x{0300}-\x{036F}] | [\x{203F}-\x{2040}] )
    )
        ^ (?! (?&NameStartChar) ) .     # not a NameStartChar at the beginning of the string
        | (?! (?&NameChar) ) .          # not a NameChar
        | : $                           # colon at end of string
    /Sux
    REGEXP;

    /**
     * @see https://www.w3.org/TR/xml/#NT-Name
     *
     * @param string $name
     * @return string
     */
    public static function escape(string $name): string
    {
        return preg_replace_callback(self::INVALID_NAME_PATTERN, function($m) {
            return sprintf('U%06X', \IntlChar::ord($m[0]));
        }, $name);
    }

    public static function unescape(string $name): string
    {
        return preg_replace_callback('/U([0-9A-Z]{6})/', function($m) {
            return \IntlChar::chr(hexdec($m[1]));
        }, $name);
    }
}
