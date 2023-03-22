<?php declare(strict_types=1);

namespace Souplette\HTML\Parser;

use Souplette\Encoding\EncodingLookup;
use Souplette\HTML\Tokenizer\Tokenizer;

final class EncodingSniffer
{
    private const BOM_RX = <<<'REGEXP'
    /^(?:
        \xEF\xBB\xBF    (*MARK:utf8)
        | \xFE\xFF      (*MARK:utf16be)
        | \xFF\xFE      (*MARK:utf16le)
    )/Sx
    REGEXP;

    public static function sniffBOM(string $input): ?string
    {
        if (preg_match(self::BOM_RX, $input, $m)) {
            return match ($m['MARK']) {
                'utf8' => EncodingLookup::UTF_8,
                'utf16be' => EncodingLookup::UTF_16BE,
                'utf16le' => EncodingLookup::UTF_16LE,
            };
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
