<?php declare(strict_types=1);

namespace Souplette\Tests;

class ResourceCollector
{
    public static function collect(string $rootPath, ?string $extension = null)
    {
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(
            $rootPath,
            \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS
        ));
        /** @var \SplFileInfo $fileInfo */
        foreach ($it as $fileInfo) {
            if ($fileInfo->isDir()) {
                continue;
            }
            if ($extension && $fileInfo->getExtension() !== $extension) {
                continue;
            }
            yield $it->getSubpathname() => $fileInfo;
        }
    }
}
