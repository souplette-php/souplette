<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\Html5Lib;

use ju1ius\HtmlParser\Encoding\Encoding;
use ju1ius\HtmlParser\Encoding\EncodingParser;
use ju1ius\HtmlParser\Encoding\EncodingSniffer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class EncodingSnifferTest extends TestCase
{
    const RESOURCE_PATH = __DIR__.'/../resources/html5lib-tests/encoding';

    /**
     * @dataProvider provide_tests1
     * @param string $input
     * @param string $expectedEncoding
     */
    public function test_tests1(string $input, string $expectedEncoding)
    {
        $this->doTest($input, $expectedEncoding);
    }

    public function provide_tests1()
    {
        yield from $this->createProvider('tests1.dat');
    }

    /**
     * @dataProvider provide_tests2
     * @param string $input
     * @param string $expectedEncoding
     */
    public function test_tests2(string $input, string $expectedEncoding)
    {
        $this->doTest($input, $expectedEncoding);
    }

    public function provide_tests2()
    {
        yield from $this->createProvider('tests2.dat');
    }

    /**
     * @dataProvider provide_test_yahoo_jp
     * @param string $input
     * @param string $expectedEncoding
     */
    public function test_test_yahoo_jp(string $input, string $expectedEncoding)
    {
        $this->doTest($input, $expectedEncoding);
    }

    public function provide_test_yahoo_jp()
    {
        yield from $this->createProvider('test-yahoo-jp.dat');
    }

    private function doTest(string $input, string $expectedEncoding)
    {
        if (strlen($input) > 1024) $this->markTestSkipped('Input too long');
        $parser = new EncodingParser();
        $encoding = $parser->parse($input) ?? Encoding::default()->encoding;
        //$encoding = EncodingSniffer::sniff($input) ?? Encoding::default()->encoding;
        Assert::assertSame($expectedEncoding, strtolower($encoding));
    }

    private function createProvider(string $fileName)
    {
        $file = new DataFile(sprintf('%s/%s', self::RESOURCE_PATH, $fileName));
        foreach ($file as $i => $test) {
            $encoding = strtolower($test['encoding']);
            yield "{$fileName} [{$i}]" => [$test['data'], $encoding];
        }
    }
}
