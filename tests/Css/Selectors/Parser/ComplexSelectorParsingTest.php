<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Parser;

use PHPUnit\Framework\Assert;
use Souplette\Css\Selectors\Node\ComplexSelector;
use Souplette\Css\Selectors\Node\CompoundSelector;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Node\Simple\TypeSelector;
use Souplette\Tests\Css\Selectors\SelectorParserTestCase;

final class ComplexSelectorParsingTest extends SelectorParserTestCase
{
    /**
     * @dataProvider parseSelectorListWithComplexSelectorsProvider
     * @param string $input
     * @param $expected
     */
    public function testParseSelectorListWithComplexSelectors(string $input, ComplexSelector $expected)
    {
        $selector = self::parseSelectorList($input);
        $expected = new SelectorList([$expected]);
        Assert::assertEquals($expected, $selector);
    }

    public function parseSelectorListWithComplexSelectorsProvider(): \Generator
    {
        foreach (['>', ' ', '+', '~', '||'] as $combinator) {
            $inputs = [
                sprintf('foo%sbar', $combinator),
                sprintf('foo %s bar', $combinator),
            ];
            foreach ($inputs as $input) {
                yield $input => [$input, new ComplexSelector(
                    new CompoundSelector([new TypeSelector('foo', '*')]),
                    $combinator,
                    new CompoundSelector([new TypeSelector('bar', '*')]),
                )];
            }
        }
    }
}
