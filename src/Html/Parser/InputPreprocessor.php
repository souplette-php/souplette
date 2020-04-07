<?php declare(strict_types=1);

namespace JoliPotage\Html\Parser;

final class InputPreprocessor
{
    private const BOM = "\u{FEFF}";
    private const BOM_LENGTH = 3; // strlen(self::BOM)

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
