<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\Html5Lib;

use ju1ius\HtmlParser\Encoding\EncodingSniffer;
use ju1ius\HtmlParser\Tests\ResourceCollector;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class EncodingSnifferTest extends TestCase
{
    const RESOURCE_PATH = __DIR__.'/../resources/html5lib-tests/encoding';

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
        $xfails = require __DIR__ . "/../resources/html5lib-xfails.php";
        foreach (ResourceCollector::collect(self::RESOURCE_PATH) as $relPath => $fileInfo) {
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
