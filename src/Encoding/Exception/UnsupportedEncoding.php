<?php declare(strict_types=1);

namespace JoliPotage\Encoding\Exception;

final class UnsupportedEncoding extends EncodingException
{
    public function __construct(string $encoding)
    {
        parent::__construct(sprintf('Unsupported encoding: %s', $encoding));
    }
}
