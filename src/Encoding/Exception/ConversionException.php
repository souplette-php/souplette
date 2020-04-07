<?php declare(strict_types=1);

namespace JoliPotage\Encoding\Exception;

final class ConversionException extends EncodingException
{
    public function __construct(string $inputEncoding, string $outputEncoding)
    {
        $msg = sprintf('Failed to convert from %s to %s.', $inputEncoding, $outputEncoding);
        parent::__construct($msg);
    }
}
