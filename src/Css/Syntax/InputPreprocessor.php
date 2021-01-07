<?php declare(strict_types=1);

namespace Souplette\Css\Syntax;

use Souplette\Encoding\Utf8Converter;

final class InputPreprocessor
{
    public static function preprocess(string $input, string $inputEncoding)
    {
        $output = Utf8Converter::convert($input, $inputEncoding);
        return strtr($output, [
            "\r\n" => "\n",
            "\r" => "\n",
            "\f" => "\n",
            "\x00" => "\u{FFFD}",
        ]);
    }
}
