<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\TreeBuilder;

use ju1ius\HtmlParser\Tests\Html5Lib\DataFile;
use ju1ius\HtmlParser\Tests\Html5Lib\TreeConstructionAssert;
use ju1ius\HtmlParser\Tests\Html5Lib\TreeConstructionTestDTO;
use PHPUnit\Framework\TestCase;

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

    public function whitespaceHandlingProvider()
    {
        $fileName = __DIR__.'/../resources/tree-construction/whitespace.dat';
        foreach (new DataFile($fileName) as $i => $test) {
            $id = "whitespace.dat::{$i}";
            $test['id'] = $id;
            yield $id => [TreeConstructionTestDTO::fromArray($test)];
        }
    }
}
