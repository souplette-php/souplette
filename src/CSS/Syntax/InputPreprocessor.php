<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax;

use Souplette\Encoding\Encoding;
use Souplette\Encoding\Utf8Converter;

final class InputPreprocessor
{
    public static function preprocess(string $input, Encoding $fromEncoding): string
    {
        $output = Utf8Converter::convert($input, $fromEncoding);
        return strtr($output, [
            "\r\n" => "\n",
            "\r" => "\n",
            "\f" => "\n",
            "\x00" => "\u{FFFD}",
        ]);
    }
}
