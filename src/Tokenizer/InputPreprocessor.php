<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tokenizer;

final class InputPreprocessor
{
    private const BOM = "\u{FEFF}";

    public static function convertToUtf8(string $input, string $fromEncoding = 'UTF-8'): string
    {
        $substitutionCharacter = mb_substitute_character();
        mb_substitute_character('none');
        $output = mb_convert_encoding($input, 'UTF-8', $fromEncoding);
        mb_substitute_character($substitutionCharacter);

        // One leading U+FEFF BYTE ORDER MARK character must be ignored if any are present.
        if (0 === substr_compare($output, self::BOM, 0, strlen(self::BOM))) {
            $output = substr($output, strlen(self::BOM));
        }

        return $output;
    }

    public static function normalizeNewlines(string $input): string
    {
        return strtr($input, [
            "\r\n" => "\n",
            "\r" => "\n",
        ]);
    }
}