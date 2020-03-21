<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\Html5Lib;

use ju1ius\HtmlParser\Parser\Parser;
use ju1ius\HtmlParser\Tests\ResourceCollector;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class TreeConstructionTest extends TestCase
{
    /**
     * @dataProvider dataFileProvider
     * @param array $test
     */
    public function testDataFile(array $test)
    {
        $input = $test['data'];
        $expected = sprintf("#document\n%s", $test['document']);
        $expectedErrors = $test['errors'];
        // TODO: script-on / script-off
        $parser = new Parser();
        $doc = $parser->parse($input, 'utf-8');
        $serializer = new Serializer();
        $result = $serializer->serialize($doc);

        Assert::assertSame($this->convertExpected($expected), $this->convertTreeDump($result));
    }

    public function dataFileProvider()
    {
        foreach ($this->collectDataFiles() as $relPath => $dataFile) {
            foreach ($dataFile as $i => $test) {
                if (isset($test['document-fragment'])) {
                    // TODO: test document fragment mode
                    continue;
                }
                $key = sprintf('%s [%s]', $relPath, $i);
                yield $key => [$test];
            }
        }
    }

    /**
     * @return \Generator|DataFile[]
     */
    private function collectDataFiles()
    {
        $rootPath = __DIR__ . '/../resources/html5lib-tests/tree-construction';
        foreach (ResourceCollector::collect($rootPath, 'dat') as $relPath => $fileInfo) {
            yield $relPath => new DataFile($fileInfo->getPathname());
        }
    }

    private function convertExpected(string $treeDump): string
    {
        return preg_replace('/^\| /m', '', $treeDump);
    }

    private function convertTreeDump(string $treeDump): string
    {
        return preg_replace('/^\|  /m', '', $treeDump);
    }
}
