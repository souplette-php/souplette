<?php declare(strict_types=1);

namespace Souplette\Tests\Html\TreeBuilder;

use PHPUnit\Framework\TestCase;
use Souplette\Tests\Html5Lib\DataFile;
use Souplette\Tests\Html5Lib\TreeConstruction\TreeConstructionAssert;
use Souplette\Tests\Html5Lib\TreeConstruction\TreeConstructionTestDTO;

final class WhitespaceTest extends TestCase
{
    /**
     * @dataProvider whitespaceHandlingProvider
     * @param TreeConstructionTestDTO $test
     */
    public function testWhitespaceHandling(TreeConstructionTestDTO $test)
    {
        TreeConstructionAssert::assertTestPasses($test);
    }

    public function whitespaceHandlingProvider(): iterable
    {
        $fileName = __DIR__.'/../../resources/tree-construction/whitespace.dat';
        foreach (new DataFile($fileName) as $i => $test) {
            $id = "whitespace.dat::{$i}";
            $test['id'] = $id;
            yield $id => [TreeConstructionTestDTO::fromArray($test)];
        }
    }
}
