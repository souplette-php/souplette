<?php declare(strict_types=1);

namespace Souplette\Html\Parser;

use Souplette\Encoding\EncodingLookup;
use Souplette\Html\Tokenizer\Tokenizer;

final class EncodingSniffer
{
    public static function sniffBOM(string $input): ?string
    {
        if (str_starts_with($input, "\xEF\xBB\xBF")) {
            return EncodingLookup::UTF_8;
        } else if (str_starts_with($input, "\xFE\xFF")) {
            return EncodingLookup::UTF_16BE;
        } else if (str_starts_with($input, "\xFF\xFE")) {
            return EncodingLookup::UTF_16LE;
        }
        return null;
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#prescan-a-byte-stream-to-determine-its-encoding
     */
    public static function sniff(string $input, int $maxLength = 1024): ?string
    {
        $charset = self::sniffBOM($input);
        if ($charset) {
            return $charset;
        }
        $parser = new MetaCharsetParser(new Tokenizer($input));
        $charset = $parser->parse();
        if ($charset) {
            return $charset;
        }

        return EncodingLookup::WINDOWS_1252;
    }
}
