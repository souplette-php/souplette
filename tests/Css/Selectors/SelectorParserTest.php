<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors;

use Souplette\Css\Selectors\Node\AttributeSelector;
use Souplette\Css\Selectors\Node\ClassSelector;
use Souplette\Css\Selectors\Node\ComplexSelector;
use Souplette\Css\Selectors\Node\CompoundSelector;
use Souplette\Css\Selectors\Node\IdSelector;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Node\TypeSelector;
use Souplette\Css\Selectors\Node\UniversalSelector;
use Souplette\Css\Selectors\SelectorParser;
use Souplette\Css\Syntax\Tokenizer\Tokenizer;
use Souplette\Css\Syntax\TokenStream\TokenStream;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class SelectorParserTest extends TestCase
{
    private static function parseSelectorList(string $input)
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

    public function parseSelectorListWithSimpleSelectorsProvider()
    {
        // Type selectors
        yield from SimpleSelectorProvider::typeSelectors();
        // ID
        yield '#id' => ['#foo', new IdSelector('foo')];
        // class
        yield '.class' => ['.bar', new ClassSelector('bar')];
        // Attributes
        yield from SimpleSelectorProvider::attributeSelectors();
    }
}
