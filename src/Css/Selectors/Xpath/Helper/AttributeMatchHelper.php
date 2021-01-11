<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Helper;

final class AttributeMatchHelper
{
    public static function attributeStartsWith(string $attribute, string $value, bool $caseInsensitive = false): string
    {
        $valueExpr = match ($caseInsensitive) {
            true => StringHelper::translateForCaseInsensitiveSearch("@{$attribute}", $value),
            false => StringHelper::toStringLiteral($value),
        };
        return sprintf('@%1$s and starts-with(@%1$s, %2$s)', $attribute, $valueExpr);
    }

    public static function attributeIncludes(string $attribute, string $value, bool $caseInsensitive = false): string
    {
        if ($caseInsensitive) {
            $value = \mb_strtolower($value, 'UTF-8');
            return sprintf(
                <<<'EOS'
                @%s and contains(concat(' ', normalize-space(%s), ' '), %s)
                EOS,
                $attribute,
                StringHelper::translateForCaseInsensitiveSearch("@{$attribute}", $value),
                StringHelper::toStringLiteral(" {$value} "),
            );
        }
        return sprintf(
            <<<'EOS'
            @%1$s and contains(concat(' ', normalize-space(@%1$s), ' '), %2$s)
            EOS,
            $attribute,
            StringHelper::toStringLiteral(" {$value} "),
        );
    }

    public static function attributeEquals(string $attribute, string $value, bool $caseInsensitive = false): string
    {
        $valueExpr = match ($caseInsensitive) {
            true => StringHelper::translateForCaseInsensitiveSearch("@{$attribute}", $value),
            false => StringHelper::toStringLiteral($value),
        };
        return sprintf('@%s = %s', $attribute, $valueExpr);
    }

    public static function attributeDashMatches(string $attribute, string $value, bool $caseInsensitive = false): string
    {
        $valueExpr = match ($caseInsensitive) {
            true => StringHelper::translateForCaseInsensitiveSearch("@{$attribute}", $value),
            false => StringHelper::toStringLiteral($value),
        };
        $prefixExpr = match ($caseInsensitive) {
            true => StringHelper::translateForCaseInsensitiveSearch("@{$attribute}", "{$value}-"),
            false => StringHelper::toStringLiteral("{$value}-"),
        };
        return sprintf(
            '@%1$s and (@%1$s = %2$s or starts-with(@%1$s, %3$s))',
            $attribute,
            $valueExpr,
            $prefixExpr,
        );
    }

    public static function attributeEndsWith(string $attribute, string $value, bool $caseInsensitive = false): string
    {
        $valueExpr = match ($caseInsensitive) {
            true => StringHelper::translateForCaseInsensitiveSearch("@{$attribute}", $value),
            false => StringHelper::toStringLiteral($value),
        };
        return sprintf(
            '@%1$s and substring(@%1$s, string-length(@%1$s)-%2$s) = %3$s',
            $attribute,
            \mb_strlen($value, 'UTF-8') - 1,
            $valueExpr,
        );
    }

    public static function attributeContains(string $attribute, string $value, bool $caseInsensitive = false): string
    {
        $valueExpr = match ($caseInsensitive) {
            true => StringHelper::translateForCaseInsensitiveSearch("@{$attribute}", $value),
            false => StringHelper::toStringLiteral($value),
        };
        return sprintf('@%1$s and contains(@%1$s, %2$s)', $attribute, $valueExpr);
    }

    public static function attributeNotEquals(string $attribute, string $value, bool $caseInsensitive = false): string
    {
        $valueExpr = match ($caseInsensitive) {
            true => StringHelper::translateForCaseInsensitiveSearch("@{$attribute}", $value),
            false => StringHelper::toStringLiteral($value),
        };
        return sprintf('not(@%1$s) or @%1$s != %2$s', $attribute, $valueExpr);
    }

}
