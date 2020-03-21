<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Encoding\Exception;

final class UnsupportedEncoding extends \RuntimeException
{
    public function __construct(string $encoding)
    {
        parent::__construct(sprintf('Unsupported encoding: %s', $encoding));
    }
}
