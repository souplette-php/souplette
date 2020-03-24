<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Encoding;

final class EncodingSniffer
{
    private const ATTR_PATTERN = <<<'REGEXP'
@
    \G
    [\t\n\f\r /]* (?P<name> [^=/>]+ )
    \s* = \s*
    (?:
        " (?P<value> [^"]* ) "
        | ' (?P<value> [^']* ) '
        | (?P<value> [^\t\n\f\r >]* )
    )
@Jx
REGEXP;

    private const MAIN_PATTERN = <<<'REGEXP'
@
    \G
    (?:
        (?P<comment> <!(?=--) .*? --> )
        | (?P<meta> <meta[\t\n\f\r /] )
        | (?P<tag> < /? [a-z] [^\t\n\f\r >]* )
        | (?P<markup> < [!?/] [^>]* > )
    )
@xi
REGEXP;

    private const META_CHARSET_PATTERN = <<<'REGEXP'
@
   charset \s* = \s*
   (?>
        " (?P<value> [^"]+ ) "
        | ' (?P<value> [^']+ ) '
        | (?P<value> [^\t\n\f\r ;]+ ) 
   ) 
@Jix
REGEXP;

    /**
     * @see https://html.spec.whatwg.org/multipage/urls-and-fetching.html#algorithm-for-extracting-a-character-encoding-from-a-meta-element
     *
     * @param string $input
     * @return string|null
     */
    public static function extractFromMetaContentAttribute(string $input): ?string
    {
        // NOTE: This method has been inlined in self::sniff()
        // Please keep the code in sync if you change the algorithm.
        if (!preg_match(self::META_CHARSET_PATTERN, $input, $matches)) {
            return null;
        }
        $label = strtolower(trim($matches['value']));
        return EncodingLookup::LABELS[$label] ?? null;
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#prescan-a-byte-stream-to-determine-its-encoding
     *
     * @param string $input
     * @param int $maxLength
     * @return string|null
     */
    public static function sniff(string $input, int $maxLength = 1024): ?string
    {
        $pos = 0;
        $length = min($maxLength, strlen($input));
        while ($pos < $length) {
            // skip until next '<' character
            $pos += strcspn($input, '<', $pos);
            if (!preg_match(self::MAIN_PATTERN, $input, $matches, PREG_UNMATCHED_AS_NULL, $pos)) {
                $pos++;
                continue;
            }
            $pos += strlen($matches[0]);
            if (isset($matches['comment'])) {
                // Advance the position pointer so that it points at the first 0x3E byte which is preceded by two 0x2D bytes
                // (i.e. at the end of an ASCII '-->' sequence) and comes after the 0x3C byte that was found.
                // (The two 0x2D bytes can be the same as those in the '<!--' sequence.)
            } elseif (isset($matches['meta'])) {
                $attributeList = [];
                $gotPragma = false;
                $needPragma = null;
                $charset = null;
                while (preg_match(self::ATTR_PATTERN, $input, $matches, 0, $pos)) {
                    $pos += strlen($matches[0]);
                    $name = strtolower($matches['name']);
                    $value = $matches['value'];
                    if (isset($attributeList[$name])) {
                        continue;
                    } else {
                        $attributeList[$name] = true;
                    }
                    if ($name === 'http-equiv' && strcasecmp($value, 'content-type') === 0) {
                        // If the attribute's value is "content-type", then set got pragma to true.
                        $gotPragma = true;
                    } elseif ($name === 'content' && $charset === null) {
                        // Apply the algorithm for extracting a character encoding from a meta element,
                        // giving the attribute's value as the string to parse.
                        if (preg_match(self::META_CHARSET_PATTERN, $value, $matches)) {
                            $label = strtolower(trim($matches['value']));
                            $charset = EncodingLookup::LABELS[$label] ?? null;
                            // If a character encoding is returned, and if charset is still set to null,
                            // let charset be the encoding returned, and set need pragma to true.
                            if ($charset) {
                                $needPragma = true;
                            }
                        }
                    } elseif ($name === 'charset') {
                        // Let charset be the result of getting an encoding from the attribute's value,
                        $label = strtolower(trim($value));
                        $charset = EncodingLookup::LABELS[$label] ?? null;
                        // and set need pragma to false.
                        $needPragma = false;
                    }
                }
                // 11. Processing: If need pragma is null, then jump to the step below labeled next byte.
                if ($needPragma === null) {
                    continue;
                }
                // 12. If need pragma is true but got pragma is false, then jump to the step below labeled next byte.
                if ($needPragma && !$gotPragma) {
                    continue;
                }
                // 13. If charset is failure, then jump to the step below labeled next byte.
                if (!$charset) {
                    continue;
                }
                // 14. If charset is a UTF-16 encoding, then set charset to UTF-8.
                if ($charset === EncodingLookup::UTF_16BE || $charset === EncodingLookup::UTF_16LE) {
                    $charset = EncodingLookup::UTF_8;
                }
                // 15. If charset is x-user-defined, then set charset to windows-1252.
                if ($charset === EncodingLookup::X_USER_DEFINED) {
                    $charset = EncodingLookup::WINDOWS_1252;
                }
                // 16. Abort the prescan a byte stream to determine its encoding algorithm,
                // returning the encoding given by charset.
                return $charset;
            } elseif (isset($matches['tag'])) {
                // 1. Advance the position pointer so that it points at the next
                // '\t', '\n', '\f', '\r', ' ' or '>' byte.
                // 2. Repeatedly get an attribute until no further attributes can be found,
                // then jump to the step below labeled next byte.
                while (preg_match(self::ATTR_PATTERN, $input, $matches, 0, $pos)) {
                    $pos += strlen($matches[0]);
                }
            } elseif (isset($matches['markup'])) {
                // Advance the position pointer so that it points at the first 0x3E byte (>)
                // that comes after the 0x3C byte that was found.
            }
        }

        return null;
    }
}
