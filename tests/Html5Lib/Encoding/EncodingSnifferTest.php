<?php declare(strict_types=1);

namespace Souplette\Tests\Html5Lib\Encoding;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Html\Parser\EncodingSniffer;
use Souplette\Tests\Html5Lib\DataFile;
use Souplette\Tests\ResourceCollector;

final class EncodingSnifferTest extends TestCase
{
    const RESOURCE_PATH = __DIR__.'/../../resources/html5lib-tests/encoding';

    /**
     * @dataProvider provideEncodingSniffing
     * @param string $input
     * @param string $expectedEncoding
     * @param string|null $skipped
     */
    public function testEncodingSniffing(string $input, string $expectedEncoding, ?string $skipped = null)
    {
        if ($skipped !== null) {
            $this->markTestSkipped($skipped);
        }
        $encoding = EncodingSniffer::sniff($input);
        Assert::assertSame($expectedEncoding, strtolower($encoding));
    }

    public function provideEncodingSniffing()
    {
        $xfails = require __DIR__ . "/../../resources/html5lib-xfails.php";
        foreach (ResourceCollector::collect(self::RESOURCE_PATH) as $relPath => $fileInfo) {
            if (str_starts_with($relPath, 'scripted/')) {
                // TODO: implement a scripting engine 😁
                continue;
            }
            $file = new DataFile($fileInfo->getPathname());
            foreach ($file as $i => $test) {
                $encoding = strtolower($test['encoding']);
                $key = "{$relPath}::{$i}";
                $skipped = $xfails['encoding'][$key] ?? null;
                yield $key => [$test['data'], $encoding, $skipped];
            }
        }
    }
}
