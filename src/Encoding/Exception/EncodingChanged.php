<?php declare(strict_types=1);

namespace Souplette\Encoding\Exception;

use Souplette\Encoding\Encoding;

final class EncodingChanged extends \RuntimeException
{
    private Encoding $encoding;

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
