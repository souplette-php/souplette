<?php declare(strict_types=1);

namespace JoliPotage\Encoding\Exception;

use JoliPotage\Encoding\Encoding;

final class EncodingChanged extends \RuntimeException
{
    private $encoding;

    public function __construct(Encoding $encoding)
    {
        $this->encoding = $encoding;
        parent::__construct();
    }

    public function getEncoding(): Encoding
    {
        return $this->encoding;
    }
}
