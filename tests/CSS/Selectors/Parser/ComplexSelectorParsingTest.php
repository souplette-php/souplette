<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Parser;

use PHPUnit\Framework\Attributes\DataProvider;
use Souplette\CSS\Selectors\Node\Combinator;
use Souplette\CSS\Selectors\Node\ComplexSelector;
use Souplette\CSS\Selectors\Node\RelationType;
use Souplette\CSS\Selectors\Node\SelectorList;
use Souplette\CSS\Selectors\Node\Simple\TypeSelector;
use Souplette\Tests\CSS\Selectors\SelectorAssert;
use Souplette\Tests\CSS\Selectors\SelectorParserTestCase;
use Souplette\Tests\CSS\Selectors\SelectorUtils;

final class ComplexSelectorParsingTest extends SelectorParserTestCase
{
    #[DataProvider('parseSelectorListWithComplexSelectorsProvider')]
    public function testParseSelectorListWithComplexSelectors(string $input, ComplexSelector $expected)
    {
        $selector = SelectorUtils::parseSelectorList($input);
        $expected = new SelectorList([$expected]);
        SelectorAssert::selectorListEquals($expected, $selector);
    }

    public static function parseSelectorListWithComplexSelectorsProvider(): iterable
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
