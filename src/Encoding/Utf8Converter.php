<?php declare(strict_types=1);

namespace Souplette\Encoding;

use Souplette\Encoding\Exception\ConversionException;
use Souplette\Encoding\Exception\UnsupportedEncoding;
use UConverter;

final class Utf8Converter
{
    public static function convert(string $input, Encoding $fromEncoding): string
    {
        $output = @UConverter::transcode($input, EncodingLookup::UTF_8, $fromEncoding->name, [
            'to_subst' => "\u{FFFD}",
        ]);
        if ($output === false) {
            throw new ConversionException($fromEncoding->name, 'UTF-8');
        }

        return $output;
    }
}
