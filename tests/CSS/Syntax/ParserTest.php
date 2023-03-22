<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Syntax;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Souplette\CSS\Syntax\Node\CSSAtRule;
use Souplette\CSS\Syntax\Node\CSSDeclaration;
use Souplette\CSS\Syntax\Node\CSSFunction;
use Souplette\CSS\Syntax\Node\CSSQualifiedRule;
use Souplette\CSS\Syntax\Node\CSSSimpleBlock;
use Souplette\CSS\Syntax\Node\CSSStylesheet;
use Souplette\CSS\Syntax\Parser;
use Souplette\CSS\Syntax\Tokenizer\Token\Colon;
use Souplette\CSS\Syntax\Tokenizer\Token\Delimiter;
use Souplette\CSS\Syntax\Tokenizer\Token\Identifier;
use Souplette\CSS\Syntax\Tokenizer\Token\Number;
use Souplette\CSS\Syntax\Tokenizer\Token\Str;
use Souplette\CSS\Syntax\Tokenizer\Token\Whitespace;
use Souplette\CSS\Syntax\Tokenizer\Tokenizer;

final class ParserTest extends TestCase
{
    #[DataProvider('parseStylesheetProvider')]
    public function testParseStylesheet(string $input, mixed $expected)
    {
        $parser = new Parser(new Tokenizer($input));
        Assert::assertEquals($expected, $parser->parseStylesheet());
    }

    public static function parseStylesheetProvider(): iterable
    {
        yield [
            '@namespace svg "//foo/bar"; baz{qux:666};',
            new CSSStylesheet([
                new CSSAtRule('namespace', [
                    new Whitespace(10),
                    new Identifier('svg', 11),
                    new Whitespace(14),
                    new Str("//foo/bar", 15),
                ]),
                new CSSQualifiedRule(
                    [new Identifier('baz', 28)],
                    new CSSSimpleBlock('{', [
                        new Identifier('qux', 32),
                        new Colon(35),
                        new Number('666', 36),
                    ])
                ),
            ]),
        ];
    }

    #[DataProvider('parseRuleListProvider')]
    public function testParseRuleList(string $input, mixed $expected)
    {
        $parser = new Parser(new Tokenizer($input));
        Assert::assertEquals($expected, $parser->parseRuleList());
    }

    public static function parseRuleListProvider(): iterable
    {
        yield [
            '@foo; bar{}',
            [
                new CSSAtRule('foo'),
                new CSSQualifiedRule(
                    prelude: [new Identifier('bar', 6)],
                    body: new CSSSimpleBlock('{')
                )
            ],
        ];
    }

    #[DataProvider('parseRuleProvider')]
    public function testParseRule(string $input, mixed $expected)
    {
        $parser = new Parser(new Tokenizer($input));
        Assert::assertEquals($expected, $parser->parseRule());
    }

    public static function parseRuleProvider(): iterable
    {
        yield ['@media(foo)', new CSSAtRule('media', [
            new CSSSimpleBlock('(', [new Identifier('foo', 7)]),
        ])];
        yield ['.foo{}', new CSSQualifiedRule(
            [new Delimiter('.', 0), new Identifier('foo', 1)],
            new CSSSimpleBlock('{'),
        )];
    }

    #[DataProvider('parseDeclarationProvider')]
    public function testParseDeclaration(string $input, mixed $expected)
    {
        $parser = new Parser(new Tokenizer($input));
        Assert::assertEquals($expected, $parser->parseDeclaration());
    }

    public static function parseDeclarationProvider(): iterable
    {
        yield ['foo: bar', new CSSDeclaration('foo', [new Identifier('bar', 5)])];
        yield ['foo: bar !IMPORTANT', new CSSDeclaration('foo', [new Identifier('bar', 5)], true)];
    }

    #[DataProvider('parseDeclarationListProvider')]
    public function testParseDeclarationList(string $input, mixed $expected)
    {
        $parser = new Parser(new Tokenizer($input));
        Assert::assertEquals($expected, $parser->parseDeclarationList());
    }

    public static function parseDeclarationListProvider(): iterable
    {
        yield ['foo:bar; baz:42', [
            new CSSDeclaration('foo', [new Identifier('bar', 4)]),
            new CSSDeclaration('baz', [new Number('42', 13)]),
        ]];
    }

    #[DataProvider('parseComponentValueProvider')]
    public function testParseComponentValue(string $input, mixed $expected)
    {
        $parser = new Parser(new Tokenizer($input));
        Assert::assertEquals($expected, $parser->parseComponentValue());
    }

    public static function parseComponentValueProvider(): iterable
    {
        yield ['foo()', new CSSFunction('foo')];
        yield ['{}', new CSSSimpleBlock('{')];
        yield ['[]', new CSSSimpleBlock('[')];
        yield ['()', new CSSSimpleBlock('(')];
        yield ['any', new Identifier('any', 0)];
    }

    #[DataProvider('parseComponentValueListProvider')]
    public function testParseComponentValueList(string $input, mixed $expected)
    {
        $parser = new Parser(new Tokenizer($input));
        Assert::assertEquals($expected, $parser->parseComponentValueList());
    }

    public static function parseComponentValueListProvider(): iterable
    {
        yield [
            'foo() anything',
            [
                new CSSFunction('foo'),
                new Whitespace(5),
                new Identifier('anything', 6),
            ],
        ];
        yield [
            '{}[]()',
            [
                new CSSSimpleBlock('{'),
                new CSSSimpleBlock('['),
                new CSSSimpleBlock('('),
            ],
        ];
    }

    #[DataProvider('parseCommaSeparatedComponentValueListProvider')]
    public function testParseCommaSeparatedComponentValueList(string $input, mixed $expected)
    {
        $parser = new Parser(new Tokenizer($input));
        Assert::assertEquals($expected, $parser->parseCommaSeparatedComponentValueList());
    }

    public static function parseCommaSeparatedComponentValueListProvider(): iterable
    {
        yield [
            'foo(),anything',
            [
                new CSSFunction('foo'),
                new Identifier('anything', 6),
            ],
        ];
        yield [
            '{},[],()',
            [
                new CSSSimpleBlock('{'),
                new CSSSimpleBlock('['),
                new CSSSimpleBlock('('),
            ],
        ];
    }
}
