<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Helper;

final class StringHelper
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

    /**
     * @see https://www.w3.org/TR/1999/REC-xpath-19991116/#function-translate
     */
    public static function translateForCaseInsensitiveSearch(string $what, string $characters): string
    {
        $characters = mb_strtolower($characters, 'UTF-8');
        $lowerChars = implode('', array_unique(mb_str_split($characters, 1, 'UTF-8')));
        $upperChars = mb_strtoupper($lowerChars, 'UTF-8');

        return sprintf(
            'translate(%s, %s, %s)',
            $what,
            StringHelper::toStringLiteral($upperChars),
            StringHelper::toStringLiteral($lowerChars),
        );
    }
}
