<?php declare(strict_types=1);

namespace Souplette\Benchmarks;

final class ResourceHelper
{
    public static function path(string $relPath): string
    {
        return __DIR__ . '/resources/' . ltrim($relPath, '/');
    }

    public static function readFile(string $relPath): string
    {
        return file_get_contents(self::path($relPath));
    }
}
