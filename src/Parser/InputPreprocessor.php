<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Parser;

use ju1ius\HtmlParser\Encoding\EncodingLookup;
use UConverter;

final class InputPreprocessor
{
    private const BOM = "\u{FEFF}";
    private const BOM_LENGTH = 3; // strlen(self::BOM)

    public static function convertToUtf8(string $input, string $fromEncoding): string
    {
        $output = @UConverter::transcode($input, EncodingLookup::UTF_8, $fromEncoding, [
            'to_subst' => "\u{FFFD}",
        ]);

        return $output;
    }

    public static function removeBOM(string $input): string
    {
        // One leading U+FEFF BYTE ORDER MARK character must be ignored if any are present.
        if (0 === substr_compare($input, self::BOM, 0, self::BOM_LENGTH)) {
            return substr($input, self::BOM_LENGTH);
        }

        return $input;
    }

    public static function normalizeNewlines(string $input): string
    {
        return strtr($input, [
            "\r\n" => "\n",
            "\r" => "\n",
        ]);
    }
}
