<?php declare(strict_types=1);

namespace Souplette\Tests\Html5Lib\TreeConstruction;

use PHPUnit\Framework\TestCase;
use Souplette\Tests\Html5Lib\DataFile;
use Souplette\Tests\ResourceCollector;

class TreeConstructionTest extends TestCase
{
    /**
     * @dataProvider dataFileProvider
     * @param TreeConstructionTestDTO $test
     */
    public function testDataFile(TreeConstructionTestDTO $test)
    {
        TreeConstructionAssert::assertTestPasses($test);
    }

    public function dataFileProvider()
    {
        $xfails = require __DIR__ . '/../../resources/html5lib-xfails.php';
        foreach ($this->collectDataFiles() as $relPath => $dataFile) {
            foreach ($dataFile as $i => $test) {
                $id = sprintf('%s::%s', $relPath, $i);
                $test['id'] = $id;
                if (isset($xfails['tree-construction'][$id])) {
                    $test['xfail'] = $xfails['tree-construction'][$id];
                }
                yield $id => [TreeConstructionTestDTO::fromArray($test)];
            }
        }
    }

    /**
     * @return \Generator|DataFile[]
     */
    private function collectDataFiles()
    {
        $rootPath = __DIR__ . '/../../resources/html5lib-tests/tree-construction';
        foreach (ResourceCollector::collect($rootPath, 'dat') as $relPath => $fileInfo) {
            if (str_starts_with($relPath, 'scripted/')) {
                // TODO: implement a scripting engine 😁
                continue;
            }
            yield $relPath => new DataFile($fileInfo->getPathname());
        }
    }
}
