<?php declare(strict_types=1);

namespace Souplette\Encoding;

use Souplette\Encoding\Exception\ConversionException;
use Souplette\Encoding\Exception\UnsupportedEncoding;
use UConverter;

final class Utf8Converter
{
    public static function convert(string $input, string $fromEncoding): string
    {
        $inputEncoding = EncodingLookup::LABELS[strtolower(trim($fromEncoding))] ?? null;
        if ($inputEncoding === null) {
            throw new UnsupportedEncoding($fromEncoding);
        }

        $output = @UConverter::transcode($input, EncodingLookup::UTF_8, $inputEncoding, [
            'to_subst' => "\u{FFFD}",
        ]);
        if ($output === false) {
            throw new ConversionException($fromEncoding, 'UTF-8');
        }

        return $output;
    }
}
