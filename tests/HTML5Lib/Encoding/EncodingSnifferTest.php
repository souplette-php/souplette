<?php declare(strict_types=1);

namespace Souplette\Tests\HTML5Lib\Encoding;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Souplette\HTML\Parser\EncodingSniffer;
use Souplette\Tests\HTML5Lib\DataFile;
use Souplette\Tests\ResourceCollector;

final class EncodingSnifferTest extends TestCase
{
    const RESOURCE_PATH = __DIR__.'/../../resources/html5lib-tests/encoding';

    #[DataProvider('encodingSniffingProvider')]
    public function testEncodingSniffing(string $input, string $expectedEncoding, ?string $skipped = null)
    {
        if ($skipped !== null) {
            $this->markTestSkipped($skipped);
        }
        $encoding = EncodingSniffer::sniff($input);
        Assert::assertSame($expectedEncoding, strtolower($encoding));
    }

    public static function encodingSniffingProvider(): iterable
    {
        $xfails = require __DIR__ . '/../../resources/html5lib-xfails.php';
        foreach (ResourceCollector::collect(self::RESOURCE_PATH) as $relPath => $fileInfo) {
            if (str_starts_with($relPath, 'scripted/')) {
                // TODO: implement a scripting engine 😁
                continue;
            }
            $file = new DataFile($fileInfo->getPathname());
            foreach ($file as $i => $test) {
                $encoding = strtolower($test['encoding']);
                $key = sprintf('%s::%d@%d', $relPath, $i, $test['metadata']['line']);
                $skipped = $xfails['encoding'][$key] ?? null;
                yield $key => [$test['data'], $encoding, $skipped];
            }
        }
    }
}
