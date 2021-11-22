<?php declare(strict_types=1);

namespace Souplette\Tests\Html5Lib\TreeConstruction;

use PHPUnit\Framework\ExpectationFailedException;
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
        if ($test->isAllowedToFail) {
            try {
                TreeConstructionAssert::assertTestPasses($test);
            } catch (ExpectationFailedException) {
                $this->markTestSkipped($test->allowedToFailReason ?? '');
            }
        } else {
            TreeConstructionAssert::assertTestPasses($test);
        }
    }

    public function dataFileProvider(): iterable
    {
        $xfails = require __DIR__ . '/../../resources/html5lib-xfails.php';
        foreach ($this->collectDataFiles() as $relPath => $dataFile) {
            foreach ($dataFile as $i => $test) {
                $id = sprintf('%s::%s', $relPath, $i);
                $key = sprintf('%s@%d', $id, $test['metadata']['line']);
                $test['id'] = $id;
                $dto = TreeConstructionTestDTO::fromArray($test);
                if ($reason = $xfails['tree-construction'][$id] ?? null) {
                    $dto->isAllowedToFail = true;
                    $dto->allowedToFailReason = $reason;
                }
                yield $key => [$dto];
            }
        }
    }

    /**
     * @return iterable<DataFile>
     */
    private function collectDataFiles(): iterable
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
