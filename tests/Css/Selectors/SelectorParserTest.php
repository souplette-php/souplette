<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\ClassSelector;
use Souplette\Css\Selectors\Node\ComplexSelector;
use Souplette\Css\Selectors\Node\CompoundSelector;
use Souplette\Css\Selectors\Node\IdSelector;
use Souplette\Css\Selectors\Node\PseudoClassSelector;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\SelectorParser;
use Souplette\Css\Syntax\Tokenizer\Tokenizer;
use Souplette\Css\Syntax\TokenStream\TokenStream;

final class SelectorParserTest extends TestCase
{
    private static function parseSelectorList(string $input): SelectorList
    {
        $tokens = new TokenStream(new Tokenizer($input), 2);
        $parser = new SelectorParser($tokens);
        return $parser->parseSelectorList();
    }

    /**
     * @dataProvider parseSelectorListWithSimpleSelectorsProvider
     * @param string $input
     * @param $expected
     */
    public function testParseSelectorListWithSimpleSelectors(string $input, $expected)
    {
        $selector = self::parseSelectorList($input);
        $expected = new SelectorList([new ComplexSelector(new CompoundSelector([$expected]))]);
        Assert::assertEquals($expected, $selector);
    }

    public function parseSelectorListWithSimpleSelectorsProvider(): \Generator
    {
        // Type selectors
        yield from SimpleSelectorProvider::typeSelectors();
        // ID
        yield '#id' => ['#foo', new IdSelector('foo')];
        // class
        yield '.class' => ['.bar', new ClassSelector('bar')];
        // Attributes
        yield from SimpleSelectorProvider::attributeSelectors();
        // pseudo-classes
        yield ':root' => [':root', new PseudoClassSelector('root')];
        yield ':first-child' => [':first-child', new PseudoClassSelector('first-child')];
        // functional pseudo-classes
        yield from SimpleSelectorProvider::simpleFunctionalPseudoClasses();
        // TODO: :is() :not() :has() :where()
    }
}
