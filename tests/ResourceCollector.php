<?php declare(strict_types=1);

namespace Souplette\Tests;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class ResourceCollector
{
    public static function path(string $relPath): string
    {
        return __DIR__ . '/resources/' . ltrim($relPath, '/');
    }

    public static function collect(string $path, ?string $pattern = null): iterable
    {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
            self::path($path),
            FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS
        ));
        /** @var SplFileInfo $fileInfo */
        foreach ($it as $fileInfo) {
            if ($fileInfo->isDir()) {
                continue;
            }
            if ($pattern && !fnmatch($pattern, $fileInfo->getPathname())) {
                continue;
            }
            yield $it->getSubpathname() => $fileInfo;
        }
    }
}
