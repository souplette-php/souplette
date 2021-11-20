<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Helper;

final class AttributeMatchHelper
{
    public static function attributeEquals(string $attribute, string $value, bool $caseInsensitive = false): string
    {
        if ($caseInsensitive) {
            return sprintf(
                '@%s and (%s = %s)',
                $attribute,
                StringHelper::translateForCaseInsensitiveSearch("@{$attribute}", $value),
                StringHelper::toStringLiteral(mb_strtolower($value, 'UTF-8')),
            );
        }
        return sprintf('@%1$s and (@%1$s = %2$s)', $attribute, StringHelper::toStringLiteral($value));
    }

    public static function attributeNotEquals(string $attribute, string $value, bool $caseInsensitive = false): string
    {
        if ($caseInsensitive) {
            return sprintf(
                'not(@%s) or %s != %s',
                $attribute,
                StringHelper::translateForCaseInsensitiveSearch("@{$attribute}", $value),
                StringHelper::toStringLiteral(mb_strtolower($value, 'UTF-8')),
            );
        }
        return sprintf('not(@%1$s) or @%1$s != %2$s', $attribute, StringHelper::toStringLiteral($value));
    }

    public static function attributeStartsWith(string $attribute, string $value, bool $caseInsensitive = false): string
    {
        if ($caseInsensitive) {
            return sprintf(
                '@%s and starts-with(%s, %s)',
                $attribute,
                StringHelper::translateForCaseInsensitiveSearch("@{$attribute}", $value),
                StringHelper::toStringLiteral(mb_strtolower($value, 'UTF-8')),
            );
        }
        return sprintf('@%1$s and starts-with(@%1$s, %2$s)', $attribute, StringHelper::toStringLiteral($value));
    }

    public static function attributeEndsWith(string $attribute, string $value, bool $caseInsensitive = false): string
    {
        if ($caseInsensitive) {
            $lower = mb_strtolower($value, 'UTF-8');
            return sprintf(
                '@%1$s and substring(%2$s, string-length(%2$s) - %3$s) = %4$s',
                $attribute,
                StringHelper::translateForCaseInsensitiveSearch("@{$attribute}", $value),
                mb_strlen($lower, 'UTF-8') - 1,
                StringHelper::toStringLiteral($lower),
            );
        }
        return sprintf(
            '@%1$s and substring(@%1$s, string-length(@%1$s) - %2$s) = %3$s',
            $attribute,
            mb_strlen($value, 'UTF-8') - 1,
            StringHelper::toStringLiteral($value),
        );
    }

    public static function attributeContains(string $attribute, string $value, bool $caseInsensitive = false): string
    {
        if ($caseInsensitive) {
            return sprintf(
                '@%s and contains(%s, %s)',
                $attribute,
                StringHelper::translateForCaseInsensitiveSearch("@{$attribute}", $value),
                StringHelper::toStringLiteral(mb_strtolower($value, 'UTF-8')),
            );
        }
        return sprintf('@%1$s and contains(@%1$s, %2$s)', $attribute, StringHelper::toStringLiteral($value));
    }

    public static function attributeIncludes(string $attribute, string $value, bool $caseInsensitive = false): string
    {
        if ($caseInsensitive) {
            $value = mb_strtolower($value, 'UTF-8');
            return sprintf(
                '@%s and contains(concat(" ", normalize-space(%s), " "), %s)',
                $attribute,
                StringHelper::translateForCaseInsensitiveSearch("@{$attribute}", $value),
                StringHelper::toStringLiteral(" {$value} "),
            );
        }
        return sprintf(
            '@%1$s and contains(concat(" ", normalize-space(@%1$s), " "), %2$s)',
            $attribute,
            StringHelper::toStringLiteral(" {$value} "),
        );
    }

    public static function attributeDashMatches(string $attribute, string $value, bool $caseInsensitive = false): string
    {
        if ($caseInsensitive) {
            $lower = mb_strtolower($value, 'UTF-8');
            return sprintf(
                '@%1$s and (%2$s = %3$s or starts-with(%2$s, %4$s))',
                $attribute,
                StringHelper::translateForCaseInsensitiveSearch("@{$attribute}", $value),
                StringHelper::toStringLiteral($lower),
                StringHelper::toStringLiteral("{$lower}-"),
            );
        }
        return sprintf(
            '@%1$s and (@%1$s = %2$s or starts-with(@%1$s, %3$s))',
            $attribute,
            StringHelper::toStringLiteral($value),
            StringHelper::toStringLiteral("{$value}-"),
        );
    }
}
