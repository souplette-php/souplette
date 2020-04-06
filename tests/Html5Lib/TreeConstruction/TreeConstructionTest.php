<?php declare(strict_types=1);

namespace JoliPotage\Tests\Html5Lib\TreeConstruction;

use JoliPotage\Tests\Html5Lib\DataFile;
use JoliPotage\Tests\ResourceCollector;
use PHPUnit\Framework\TestCase;

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
            if (strpos($relPath,'scripted/') === 0) {
                // TODO: implement a scripting engine 😁
                continue;
            }
            yield $relPath => new DataFile($fileInfo->getPathname());
        }
    }
}
