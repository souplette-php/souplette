<?php declare(strict_types=1);

namespace Souplette\Encoding\Exception;

use Souplette\Encoding\Encoding;

final class EncodingChanged extends \RuntimeException
{
    public function __construct(
        public readonly Encoding $encoding,
    ) {
        parent::__construct();
    }
}
