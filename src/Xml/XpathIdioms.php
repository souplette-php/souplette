<?php declare(strict_types=1);

namespace Souplette\Xml;

final class XpathIdioms
{
    public static function toStringLiteral(string $value): string
    {
        if (!str_contains($value, "'")) {
            return "'{$value}'";
        }

        if (!str_contains($value, '"')) {
            return sprintf('"%s"', $value);
        }

        $string = $value;
        $parts = [];
        while (true) {
            if (false !== $pos = strpos($string, "'")) {
                $parts[] = sprintf("'%s'", substr($string, 0, $pos));
                $parts[] = '"\'"';
                $string = substr($string, $pos + 1);
            } else {
                $parts[] = "'{$string}'";
                break;
            }
        }

        return sprintf('concat(%s)', implode(', ', $parts));
    }

    public static function attributeIncludes(string $attribute, string $value): string
    {
        $predicate = <<<'EOS'
        @%1$s and contains(concat(' ', normalize-space(@%1$s), ' '), %2$s)
        EOS;

        return sprintf(
            $predicate,
            $attribute,
            self::toStringLiteral(" {$value} "),
        );
    }

    public static function attributeEquals(string $attribute, string $value): string
    {
        return sprintf(
            '@%s = %s',
            $attribute,
            self::toStringLiteral($value),
        );
    }

    public static function attributeNotEquals(string $attribute, string $value): string
    {
        return sprintf(
            'not(@%1$s) or @%1$s != %2$s',
            $attribute,
            self::toStringLiteral($value),
        );
    }

    public static function attributeStartsWith(string $attribute, string $value): string
    {
        return sprintf(
            '@%1$s and starts-with(@%1$s, %2$s)',
            $attribute,
            self::toStringLiteral($value),
        );
    }

    public static function attributeEndsWith(string $attribute, string $value): string
    {
        return sprintf(
            '@%1$s and substring(@%1$s, string-length(@%1$s)-%2$s) = %3$s',
            $attribute,
            strlen($value) - 1,
            self::toStringLiteral($value),
        );
    }

    public static function attributeContains(string $attribute, string $value): string
    {
        return sprintf(
            '@%1$s and contains(@%1$s, %2$s)',
            $attribute,
            self::toStringLiteral($value),
        );
    }

    public static function attributeDashMatches(string $attribute, string $value): string
    {
        return sprintf(
            '@%1$s and (@%1$s = %2$s or starts-with(@%1$s, %3$s))',
            $attribute,
            self::toStringLiteral($value),
            self::toStringLiteral("{$value}-"),
        );
    }
}
