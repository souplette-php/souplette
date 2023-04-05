<?php declare(strict_types=1);

namespace Souplette\Tests\HTML5Lib\TreeConstruction;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Souplette\Tests\HTML5Lib\DataFile;
use Souplette\Tests\HTML5Lib\Failures\ExpectedFailures;
use Souplette\Tests\ResourceCollector;

class TreeConstructionTest extends TestCase
{
    /**
     * @param TreeConstructionTestDTO $test
     */
    #[DataProvider('dataFileProvider')]
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

    public static function dataFileProvider(): iterable
    {
        $xfails = require __DIR__ . '/../../resources/html5lib-xfails.php';
        foreach (self::collectDataFiles() as $relPath => $dataFile) {
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
    private static function collectDataFiles(): iterable
    {
        foreach (ResourceCollector::collect('html5lib-tests/tree-construction', '*.dat') as $relPath => $fileInfo) {
            if (str_starts_with($relPath, 'scripted/')) {
                // TODO: implement a scripting engine 😁
                continue;
            }
            yield $relPath => new DataFile($fileInfo->getPathname());
        }
    }
}
