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
        yield 'namespaced element' => ['foo|bar', new TypeSelector('bar', 'foo')];
        yield 'element in any namespace' => ['*|foo', new TypeSelector('foo', '*')];
        yield 'any element in any namespace' => ['*|*', new UniversalSelector()];
        yield 'element with no namespace' => ['|bar', new TypeSelector('bar', null)];
        // FIXME: check this type selector `|*`
        //yield 'any element with no namespace' => ['|*', new TypeSelector('*', null)];
        yield 'element without explicit namespace' => ['foo', new TypeSelector('foo', '*')];
        yield '#id' => ['#foo', new IdSelector('foo')];
        yield '.class' => ['.bar', new ClassSelector('bar')];
        yield 'attribute exists' => ['[disabled]', new AttributeSelector('disabled')];
        yield 'attribute equals (string)' => [
            '[foo="bar"]', new AttributeSelector('foo', '*', AttributeSelector::OPERATOR_EQUALS, 'bar')
        ];
        yield 'attribute equals (identifier)' => [
            '[foo=bar]', new AttributeSelector('foo', '*', AttributeSelector::OPERATOR_EQUALS, 'bar')
        ];
        yield 'attribute includes (string)' => [
            '[foo~="bar"]', new AttributeSelector('foo', '*', AttributeSelector::OPERATOR_INCLUDES, 'bar')
        ];
        yield 'attribute includes (identifier)' => [
            '[foo~=bar]', new AttributeSelector('foo', '*', AttributeSelector::OPERATOR_INCLUDES, 'bar')
        ];
        yield 'attribute dash match (string)' => [
            '[foo|="bar"]', new AttributeSelector('foo', '*', AttributeSelector::OPERATOR_DASH_MATCH, 'bar')
        ];
        yield 'attribute dash match (identifier)' => [
            '[foo|=bar]', new AttributeSelector('foo', '*', AttributeSelector::OPERATOR_DASH_MATCH, 'bar')
        ];
        yield 'attribute prefix match (string)' => [
            '[foo^="bar"]', new AttributeSelector('foo', '*', AttributeSelector::OPERATOR_PREFIX_MATCH, 'bar')
        ];
        yield 'attribute prefix match (identifier)' => [
            '[foo^=bar]', new AttributeSelector('foo', '*', AttributeSelector::OPERATOR_PREFIX_MATCH, 'bar')
        ];
        yield 'attribute suffix match (string)' => [
            '[foo$="bar"]', new AttributeSelector('foo', '*', AttributeSelector::OPERATOR_SUFFIX_MATCH, 'bar')
        ];
        yield 'attribute suffix match (identifier)' => [
            '[foo$=bar]', new AttributeSelector('foo', '*', AttributeSelector::OPERATOR_SUFFIX_MATCH, 'bar')
        ];
        yield 'attribute substring match (string)' => [
            '[foo*="bar"]', new AttributeSelector('foo', '*', AttributeSelector::OPERATOR_SUBSTRING_MATCH, 'bar')
        ];
        yield 'attribute substring match (identifier)' => [
            '[foo*=bar]', new AttributeSelector('foo', '*', AttributeSelector::OPERATOR_SUBSTRING_MATCH, 'bar')
        ];
    }
}
