<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax;

final class Utils
{
    private const UNESCAPE_STRING_PATTERN = <<<'REGEXP'
    /
        \\ (?:
            (?<unicode> [a-f0-9]{1,6} \s? )
            | (?<ignored> \n )
            | (?<any> . )
        )
    /xi
    REGEXP;

    public static function unescapeString(string $input): string
    {
        if (!str_contains($input, '\\')) {
            return $input;
        }

        return preg_replace_callback(self::UNESCAPE_STRING_PATTERN, 'self::unescapeCodepointCallback', $input);
    }

    private static function unescapeCodepointCallback(array $matches): string
    {
        if (!empty($matches['ignored'])) {
            return '';
        } elseif (!empty($matches['any'])) {
            return $matches['any'];
        } elseif (!empty($matches['unicode'])) {
            $cp = hexdec($matches['unicode']);
            if ($cp === 0 || $cp > 0x10FFFF || ($cp >= 0xD800 && $cp <= 0xDFFF)) {
                // null, outside unicode or surrogate
                return "\u{FFFD}";
            }
            return \IntlChar::chr($cp);
        }
        return "\u{FFFD}";
    }
}
