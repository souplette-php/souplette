<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Syntax;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Css\Syntax\Tokenizer\Token\AtKeyword;
use Souplette\Css\Syntax\Tokenizer\Token\Comma;
use Souplette\Css\Syntax\Tokenizer\Token\Delimiter;
use Souplette\Css\Syntax\Tokenizer\Token\Dimension;
use Souplette\Css\Syntax\Tokenizer\Token\Hash;
use Souplette\Css\Syntax\Tokenizer\Token\Identifier;
use Souplette\Css\Syntax\Tokenizer\Token\Number;
use Souplette\Css\Syntax\Tokenizer\Token\Percentage;
use Souplette\Css\Syntax\Tokenizer\Token\Str;
use Souplette\Css\Syntax\Tokenizer\Token\Whitespace;
use Souplette\Css\Syntax\Tokenizer\Tokenizer;

final class TokenizerTest extends TestCase
{
    private static function assertTokenizationResult(string $input, array $expected)
    {
        $tokenizer = new Tokenizer($input);
        $tokens = iterator_to_array($tokenizer);
        array_pop($tokens);

        Assert::assertEquals($expected, $tokens);
    }

    /**
     * @dataProvider commentsProvider
     */
    public function testComments(string $input, array $expected)
    {
        self::assertTokenizationResult($input, $expected);
    }

    public function commentsProvider()
    {
        yield 'skips lone comment' => ['/* foo */', []];
        yield 'skip comments' => ['foo/* nope *//* neither */bar', [
            new Identifier('foo', 0),
            new Identifier('bar', 26),
        ]];
        yield 'stop comment at first */' => ['/* */ident*/', [
            new Identifier('ident', 5),
            new Delimiter('*', 10),
            new Delimiter('/', 11),
        ]];
    }

    /**
     * @dataProvider whitespaceProvider
     */
    public function testWhitespace(string $input, array $expected)
    {
        self::assertTokenizationResult($input, $expected);
    }

    public function whitespaceProvider()
    {
        yield 'aggregates whitespace' => ["  \n\t   ", [
            new Whitespace(0),
        ]];
    }

    /**
     * @dataProvider stringsProvider
     */
    public function testStrings(string $input, array $expected)
    {
        self::assertTokenizationResult($input, $expected);
    }

    public function stringsProvider()
    {
        yield 'double quoted string' => ['"foo bar"', [
            new Str('foo bar', 0),
        ]];
        yield 'single quoted string' => ["'foo bar'", [
            new Str('foo bar', 0),
        ]];
        yield 'two adjacent strings' => ["'foo bar' 'baz qux'", [
            new Str('foo bar', 0),
            new Whitespace(9),
            new Str('baz qux', 10),
        ]];
    }

    /**
     * @dataProvider stringEscapesProvider
     */
    public function testStringEscapes(string $input, string $expectedValue, string $expectedRepr)
    {

        $tokenizer = new Tokenizer($input);
        $token = $tokenizer->consumeToken();
        Assert::assertInstanceOf(Str::class, $token);
        Assert::assertSame($expectedValue, $token->value);
        Assert::assertSame($expectedRepr, $token->representation);
    }

    public function stringEscapesProvider()
    {
        yield 'escaped double quote' => ['"foo\\"bar"', 'foo"bar', '"foo\\"bar"'];
        yield 'escaped single quote' => ["'foo\\'bar'", "foo'bar", '"foo\\\'bar"'];
        yield 'escaped newline' => ["'foo\\\nbar'", "foobar", sprintf('"foo\%sbar"', "\n")];
        yield 'unicode escape' => ['"poo\\1F4A9 bar"', 'poo💩bar', '"poo\\1F4A9 bar"'];
    }

    /**
     * @dataProvider tokenizationProvider
     */
    public function testTokenization(string $input, array $expected)
    {
        self::assertTokenizationResult($input, $expected);
    }

    public function tokenizationProvider()
    {
        yield 'at-keyword' => ['@foo', [
            new AtKeyword('foo', 0),
        ]];
        yield 'percentage' => ['.25%', [
            new Percentage('.25', 0)
        ]];
        yield 'tricky :nth-child() arguments' => ['2n+3 of .foo,#bar', [
            new Dimension('2', 'n', 0),
            new Number('+3', 2),
            new Whitespace(4),
            new Identifier('of', 5),
            new Whitespace(7),
            new Delimiter('.', 8),
            new Identifier('foo', 9),
            new Comma(12),
            new Hash('bar', 13, true),
        ]];
        yield 'high codepoints' => ["#😀 @😋", [
            new Hash("😀", 0, true),
            new Whitespace(5),
            new AtKeyword('😋', 6),
        ]];
        yield ['u/**/+0a/**/?', [
            new Identifier('u', 0),
            new Dimension('+0', 'a', 5),
            new Delimiter('?', 12),
        ]];
    }
}
