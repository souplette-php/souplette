<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Syntax;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Css\Syntax\Node\CssAtRule;
use Souplette\Css\Syntax\Node\CssDeclaration;
use Souplette\Css\Syntax\Node\CssFunction;
use Souplette\Css\Syntax\Node\CssQualifiedRule;
use Souplette\Css\Syntax\Node\CssSimpleBlock;
use Souplette\Css\Syntax\Node\CssStylesheet;
use Souplette\Css\Syntax\Parser;
use Souplette\Css\Syntax\Tokenizer\Token\Colon;
use Souplette\Css\Syntax\Tokenizer\Token\Delimiter;
use Souplette\Css\Syntax\Tokenizer\Token\Identifier;
use Souplette\Css\Syntax\Tokenizer\Token\Number;
use Souplette\Css\Syntax\Tokenizer\Token\Str;
use Souplette\Css\Syntax\Tokenizer\Token\Whitespace;
use Souplette\Css\Syntax\Tokenizer\Tokenizer;

final class ParserTest extends TestCase
{
    /**
     * @dataProvider parseStylesheetProvider
     */
    public function testParseStylesheet(string $input, mixed $expected)
    {
        $parser = new Parser(new Tokenizer($input));
        Assert::assertEquals($expected, $parser->parseStylesheet());
    }

    public function parseStylesheetProvider(): iterable
    {
        yield [
            '@namespace svg "//foo/bar"; baz{qux:666};',
            new CssStylesheet([
                new CssAtRule('namespace', [
                    new Whitespace(10),
                    new Identifier('svg', 11),
                    new Whitespace(14),
                    new Str("//foo/bar", 15),
                ]),
                new CssQualifiedRule(
                    [new Identifier('baz', 28)],
                    new CssSimpleBlock('{', [
                        new Identifier('qux', 32),
                        new Colon(35),
                        new Number('666', 36),
                    ])
                ),
            ]),
        ];
    }

    /**
     * @dataProvider parseRuleListProvider
     */
    public function testParseRuleList(string $input, mixed $expected)
    {
        $parser = new Parser(new Tokenizer($input));
        Assert::assertEquals($expected, $parser->parseRuleList());
    }

    public function parseRuleListProvider(): iterable
    {
        yield [
            '@foo; bar{}',
            [
                new CssAtRule('foo'),
                new CssQualifiedRule(
                    prelude: [new Identifier('bar', 6)],
                    body: new CssSimpleBlock('{')
                )
            ],
        ];
    }

    /**
     * @dataProvider parseRuleProvider
     */
    public function testParseRule(string $input, mixed $expected)
    {
        $parser = new Parser(new Tokenizer($input));
        Assert::assertEquals($expected, $parser->parseRule());
    }

    public function parseRuleProvider(): iterable
    {
        yield ['@media(foo)', new CssAtRule('media', [
            new CssSimpleBlock('(', [new Identifier('foo', 7)]),
        ])];
        yield ['.foo{}', new CssQualifiedRule(
            [new Delimiter('.', 0), new Identifier('foo', 1)],
            new CssSimpleBlock('{'),
        )];
    }

    /**
     * @dataProvider parseDeclarationProvider
     */
    public function testParseDeclaration(string $input, mixed $expected)
    {
        $parser = new Parser(new Tokenizer($input));
        Assert::assertEquals($expected, $parser->parseDeclaration());
    }

    public function parseDeclarationProvider(): iterable
    {
        yield ['foo: bar', new CssDeclaration('foo', [new Identifier('bar', 5)])];
        yield ['foo: bar !IMPORTANT', new CssDeclaration('foo', [new Identifier('bar', 5)], true)];
    }

    /**
     * @dataProvider parseDeclarationListProvider
     */
    public function testParseDeclarationList(string $input, mixed $expected)
    {
        $parser = new Parser(new Tokenizer($input));
        Assert::assertEquals($expected, $parser->parseDeclarationList());
    }

    public function parseDeclarationListProvider(): iterable
    {
        yield ['foo:bar; baz:42', [
            new CssDeclaration('foo', [new Identifier('bar', 4)]),
            new CssDeclaration('baz', [new Number('42', 13)]),
        ]];
    }

    /**
     * @dataProvider parseComponentValueProvider
     */
    public function testParseComponentValue(string $input, mixed $expected)
    {
        $parser = new Parser(new Tokenizer($input));
        Assert::assertEquals($expected, $parser->parseComponentValue());
    }

    public function parseComponentValueProvider(): iterable
    {
        yield ['foo()', new CssFunction('foo')];
        yield ['{}', new CssSimpleBlock('{')];
        yield ['[]', new CssSimpleBlock('[')];
        yield ['()', new CssSimpleBlock('(')];
        yield ['any', new Identifier('any', 0)];
    }

    /**
     * @dataProvider parseComponentValueListProvider
     */
    public function testParseComponentValueList(string $input, mixed $expected)
    {
        $parser = new Parser(new Tokenizer($input));
        Assert::assertEquals($expected, $parser->parseComponentValueList());
    }

    public function parseComponentValueListProvider(): iterable
    {
        yield [
            'foo() anything',
            [
                new CssFunction('foo'),
                new Whitespace(5),
                new Identifier('anything', 6),
            ],
        ];
        yield [
            '{}[]()',
            [
                new CssSimpleBlock('{'),
                new CssSimpleBlock('['),
                new CssSimpleBlock('('),
            ],
        ];
    }

    /**
     * @dataProvider parseCommaSeparatedComponentValueListProvider
     */
    public function testParseCommaSeparatedComponentValueList(string $input, mixed $expected)
    {
        $parser = new Parser(new Tokenizer($input));
        Assert::assertEquals($expected, $parser->parseCommaSeparatedComponentValueList());
    }

    public function parseCommaSeparatedComponentValueListProvider(): iterable
    {
        yield [
            'foo(),anything',
            [
                new CssFunction('foo'),
                new Identifier('anything', 6),
            ],
        ];
        yield [
            '{},[],()',
            [
                new CssSimpleBlock('{'),
                new CssSimpleBlock('['),
                new CssSimpleBlock('('),
            ],
        ];
    }
}
