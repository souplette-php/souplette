<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Codegen;

final class Utils
{
    const ASCII_CNTRL_ESCAPES = [
        "\0" => '\0',
        "\n" => '\n',
        "\t" => '\t',
        "\f" => '\f',
        "\r" => '\r',
        "\v" => '\e',
        "\e" => '\e',
    ];

    public static function escapeAsciiControl(string $byte): string
    {
        if (isset(self::ASCII_CNTRL_ESCAPES[$byte])) {
            return self::ASCII_CNTRL_ESCAPES[$byte];
        }
        return sprintf('\x%02x', ord($byte));
    }
}