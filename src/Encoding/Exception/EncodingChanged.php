<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Encoding\Exception;

use ju1ius\HtmlParser\Encoding\Encoding;

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
