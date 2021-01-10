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

    public static function attributeIncludes(string $attribute, string $value, bool $caseInsensitive = false): string
    {
        if ($caseInsensitive) {
            $value = \mb_strtolower($value, 'UTF-8');
            return sprintf(
                <<<'EOS'
                @%s and contains(concat(' ', normalize-space(%s), ' '), %s)
                EOS,
                $attribute,
                self::translateForCaseInsensitiveSearch("@{$attribute}", $value),
                self::toStringLiteral(" {$value} "),
            );
        }
        return sprintf(
            <<<'EOS'
            @%1$s and contains(concat(' ', normalize-space(@%1$s), ' '), %2$s)
            EOS,
            $attribute,
            self::toStringLiteral(" {$value} "),
        );
    }

    public static function attributeEquals(string $attribute, string $value, bool $caseInsensitive = false): string
    {
        $valueExpr = match($caseInsensitive) {
            true => self::translateForCaseInsensitiveSearch("@{$attribute}", $value),
            false => self::toStringLiteral($value),
        };
        return sprintf('@%s = %s', $attribute, $valueExpr);
    }

    public static function attributeNotEquals(string $attribute, string $value, bool $caseInsensitive = false): string
    {
        $valueExpr = match($caseInsensitive) {
            true => self::translateForCaseInsensitiveSearch("@{$attribute}", $value),
            false => self::toStringLiteral($value),
        };
        return sprintf('not(@%1$s) or @%1$s != %2$s', $attribute, $valueExpr);
    }

    public static function attributeStartsWith(string $attribute, string $value, bool $caseInsensitive = false): string
    {
        $valueExpr = match($caseInsensitive) {
            true => self::translateForCaseInsensitiveSearch("@{$attribute}", $value),
            false => self::toStringLiteral($value),
        };
        return sprintf('@%1$s and starts-with(@%1$s, %2$s)', $attribute, $valueExpr);
    }

    public static function attributeEndsWith(string $attribute, string $value, bool $caseInsensitive = false): string
    {
        $valueExpr = match($caseInsensitive) {
            true => self::translateForCaseInsensitiveSearch("@{$attribute}", $value),
            false => self::toStringLiteral($value),
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
        $valueExpr = match($caseInsensitive) {
            true => self::translateForCaseInsensitiveSearch("@{$attribute}", $value),
            false => self::toStringLiteral($value),
        };
        return sprintf('@%1$s and contains(@%1$s, %2$s)', $attribute, $valueExpr);
    }

    public static function attributeDashMatches(string $attribute, string $value, bool $caseInsensitive = false): string
    {
        $valueExpr = match($caseInsensitive) {
            true => self::translateForCaseInsensitiveSearch("@{$attribute}", $value),
            false => self::toStringLiteral($value),
        };
        $prefixExpr = match($caseInsensitive) {
            true => self::translateForCaseInsensitiveSearch("@{$attribute}", "{$value}-"),
            false => self::toStringLiteral("{$value}-"),
        };
        return sprintf(
            '@%1$s and (@%1$s = %2$s or starts-with(@%1$s, %3$s))',
            $attribute,
            $valueExpr,
            $prefixExpr,
        );
    }

    /**
     * @see https://www.w3.org/TR/1999/REC-xpath-19991116/#function-translate
     */
    private static function translateForCaseInsensitiveSearch(string $what, string $characters): string
    {
        $characters = \mb_strtolower($characters, 'UTF-8');
        $lowerChars = implode('', array_unique(\mb_str_split($characters, 1, 'UTF-8')));
        $upperChars = \mb_strtoupper($lowerChars, 'UTF-8');

        return sprintf(
            'translate(%s, %s, %s)',
            $what,
            self::toStringLiteral($upperChars),
            self::toStringLiteral($lowerChars),
        );
    }
}
