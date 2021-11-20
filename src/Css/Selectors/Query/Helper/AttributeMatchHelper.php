<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Helper;

/**
 * @see https://www.w3.org/TR/selectors-4/#attribute-selectors
 */
final class AttributeMatchHelper
{
    public static function equals(string $expected, string $actual, bool $caseInsensitive = false): bool
    {
        return match ($caseInsensitive) {
            true => strcasecmp($expected, $actual) === 0,
            false => $expected === $actual,
        };
    }

    public static function dashMatch(string $expected, string $actual, bool $caseInsensitive = false): bool
    {
        $length = strcspn($actual, '-', 0);
        $actual = substr($actual, 0, $length);
        return match ($caseInsensitive) {
            true => strcasecmp($actual, $expected) === 0,
            false => $expected === $actual,
        };
    }

    public static function includes(string $needle, string $haystack, bool $caseInsensitive = false): bool
    {
        $ws = " \t\n\r\f";
        $token = strtok($haystack, $ws);
        while ($token) {
            if (match ($caseInsensitive) {
                true => strcasecmp($token, $needle) === 0,
                false => $token === $needle,
            }) return true;
            $token = strtok($ws);
        }
        return false;
    }

    public static function prefixMatch(string $expected, string $actual, bool $caseInsensitive = false): bool
    {
        return match ($caseInsensitive) {
            true => strncasecmp($actual, $expected, \strlen($expected)) === 0,
            false => str_starts_with($actual, $expected),
        };
    }

    public static function suffixMatch(string $expected, string $actual, bool $caseInsensitive = false): bool
    {
        return match ($caseInsensitive) {
            true => substr_compare($actual, $expected, -\strlen($expected), null, true) === 0,
            false => str_ends_with($actual, $expected),
        };
    }

    public static function substring(string $expected, string $actual, bool $caseInsensitive = false): bool
    {
        return match ($caseInsensitive) {
            true => stripos($actual, $expected) !== false,
            false => str_contains($actual, $expected),
        };
    }

    public static function boolean(\DOMElement $element, string $attr): bool
    {
        if (!$element->hasAttribute($attr)) {
            return false;
        }
        $value = $element->getAttribute($attr);
        return !$value || strcasecmp($attr, $value) === 0;
    }
}
