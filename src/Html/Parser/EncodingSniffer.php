<?php declare(strict_types=1);

namespace JoliPotage\Html\Parser;

use JoliPotage\Encoding\EncodingLookup;
use JoliPotage\Html\Parser\MetaCharsetParser;
use JoliPotage\Html\Parser\Tokenizer\Tokenizer;

final class EncodingSniffer
{
    private const BOM_PATTERN = <<<'REGEXP'
/
    ^
    (?<utf8> \xEF \xBB \xBF )
    | (?<utf16be> \xFE \xFF ) 
    | (?<utf16le> \xFF \xFE ) 
/x
REGEXP;

    public static function sniffBOM(string $input): ?string
    {
        if (substr_compare($input, "\xEF\xBB\xBF", 0, 3) === 0) {
            return EncodingLookup::UTF_8;
        } elseif (substr_compare($input, "\xFE\xFF", 0, 2) === 0) {
            return EncodingLookup::UTF_16BE;
        } elseif (substr_compare($input, "\xFF\xFE", 0, 2) === 0) {
            return EncodingLookup::UTF_16LE;
        }
        return null;
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
        $charset = self::sniffBOM($input);
        if ($charset) {
            return $charset;
        }
        $parser = new MetaCharsetParser(new Tokenizer($input));
        $charset = $parser->parse();
        if ($charset) {
            return $charset;
        }

        return EncodingLookup::WINDOWS_1252;
    }
}
