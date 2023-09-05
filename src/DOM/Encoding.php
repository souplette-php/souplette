<?php declare(strict_types=1);

namespace Souplette\DOM;

use Souplette\DOM\Exception\EncodingError;

final class Encoding
{
    public static function ensureUtf8(string $data): string
    {
        if (!mb_check_encoding($data, 'utf-8')) {
            throw new EncodingError('Invalid UTF-8');
        }
        return $data;
    }
}
