<?php declare(strict_types=1);

namespace Souplette\Dom\Internal;

final class Idioms
{
    const ASCII_WHITESPACE = " \n\t\r\f";

    public static function splitInputOnAsciiWhitespace(string $input): iterable
    {
        $token = strtok($input, self::ASCII_WHITESPACE);
        $i = 0;
        while ($token) {
            yield $i++ => $token;
            $token = strtok(self::ASCII_WHITESPACE);
        }
    }
}
