<?php declare(strict_types=1);

namespace Souplette\Html\Parser;

use JetBrains\PhpStorm\Pure;

final class InputPreprocessor
{
    private const BOM = "\u{FEFF}";

    #[Pure]
    public static function removeBOM(string $input): string
    {
        // One leading U+FEFF BYTE ORDER MARK character must be ignored if any are present.
        if (str_starts_with($input, self::BOM)) {
            return substr($input, \strlen(self::BOM));
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
