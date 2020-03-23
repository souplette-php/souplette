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

    public static function iterateCodepoints(string $input)
    {
        $it = \IntlBreakIterator::createCodePointInstance();
        $it->setText($input);
        foreach ($it->getPartsIterator(\IntlPartsIterator::KEY_LEFT) as $offset => $char) {
            yield $offset => $char;
        }
    }

    public static function downloadFile(string $url, string $destination)
    {
        $stream = fopen($url, 'r');
        if ($stream === false) {
            throw new \RuntimeException(sprintf(
                'Could not open url: %s',
                $url
            ));
        }
        $size = file_put_contents($destination, $stream);
        if ($size === false) {
            throw new \RuntimeException(sprintf(
                'Could not write to destination: %s',
                $destination
            ));
        }
    }

    public static function sourcePositionToOffset(string $source, int $lineno, int $col): int
    {
        if ($lineno === 1) {
            return $col - 1;
        }

        $lines = preg_split('/\r\n|\n/', $source, -1, PREG_SPLIT_OFFSET_CAPTURE);
        $line = $lines[$lineno - 1] ?? null;
        if ($line === null) {
            throw new \RuntimeException("No such line: $lineno");
        }
        [$text, $offset] = $line;

        return max(0, min($offset + $col - 1, strlen($source)));
    }
}
