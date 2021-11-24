<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Parser;

use Souplette\Css\Selectors\Node\Combinator;
use Souplette\Css\Selectors\Node\ComplexSelector;
use Souplette\Css\Selectors\Node\RelationType;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Node\Simple\TypeSelector;
use Souplette\Tests\Css\Selectors\SelectorAssert;
use Souplette\Tests\Css\Selectors\SelectorParserTestCase;
use Souplette\Tests\Css\Selectors\Utils;

final class ComplexSelectorParsingTest extends SelectorParserTestCase
{
    /**
     * @dataProvider parseSelectorListWithComplexSelectorsProvider
     */
    public function testParseSelectorListWithComplexSelectors(string $input, ComplexSelector $expected)
    {
        $selector = Utils::parseSelectorList($input);
        $expected = new SelectorList([$expected]);
        SelectorAssert::selectorListEquals($expected, $selector);
    }

    public function parseSelectorListWithComplexSelectorsProvider(): iterable
    {
        foreach (Combinator::cases() as $combinator) {
            $inputs = [
                sprintf('foo%sbar', $combinator->value),
                sprintf('foo %s bar', $combinator->value),
            ];
            foreach ($inputs as $input) {
                $foo = new TypeSelector('foo', '*');
                $bar = new TypeSelector('bar', '*');
                $bar->next = $foo;
                $bar->relationType = RelationType::fromCombinator($combinator);
                yield $input => [$input, new ComplexSelector($bar)];
            }
        }
    }
}
