<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\Html5Lib;

use ju1ius\HtmlParser\Namespaces;
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
        //if (!isset($test['document-fragment'])) {
        //    $this->markTestSkipped('Document fragment parsing not yet implemented.');
        //}
        if (isset($test['script-on'])) {
            $this->markTestSkipped('Scripting flag not yet implemented.');
        }
        $input = $test['data'];
        $expectedErrors = $test['errors'];
        $fragment = $test['document-fragment'] ?? null;
        // TODO: script-on / script-off
        $parser = new Parser();
        $serializer = new Serializer();
        if ($fragment) {
            $doc = new \DOMDocument();
            $context = explode(' ', trim($fragment));
            if (count($context) === 2) {
                [$prefix, $localName] = $context;
                $context = $doc->createElementNS(Namespaces::NAMESPACES[$prefix], $localName);
            } else {
                [$localName] = $context;
                $context = $doc->createElementNS(Namespaces::HTML, $localName);
            }
            $nodes = $parser->parseFragment($context, $input, 'utf-8');
            $frag = $doc->createDocumentFragment();
            foreach ($nodes as $node) {
                $node = $doc->importNode($node, true);
                $frag->appendChild($node);
            }
            $expected = sprintf("#document-fragment\n%s", $test['document']);
            $result = $serializer->serialize($frag);
        } else {
            $expected = sprintf("#document\n%s", $test['document']);
            $doc = $parser->parse($input, 'utf-8');
            $result = $serializer->serialize($doc);
        }

        Assert::assertSame($this->convertExpected($expected), $this->convertTreeDump($result));
    }

    public function dataFileProvider()
    {
        foreach ($this->collectDataFiles() as $relPath => $dataFile) {
            foreach ($dataFile as $i => $test) {
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
        //yield 'tests1.dat' => new DataFile($rootPath.'/tests1.dat');
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
