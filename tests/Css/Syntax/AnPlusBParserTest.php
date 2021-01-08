<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Syntax;

use Souplette\Css\Syntax\AnPlusBParser;
use Souplette\Css\Syntax\AnPlusBStringParser;
use Souplette\Css\Syntax\Exception\ParseError;
use Souplette\Css\Syntax\Node\AnPlusB;
use Souplette\Css\Syntax\Tokenizer\Tokenizer;
use Souplette\Css\Syntax\TokenStream\TokenStream;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class AnPlusBParserTest extends TestCase
{
    private static function parse(string $input): AnPlusB
    {
        $tokenizer = new Tokenizer($input);
        $parser = new AnPlusBParser(new TokenStream($tokenizer, 1));
        return $parser->parse();
    }

    /**
     * @dataProvider parseProvider
     */
    public function testParse(string $input, int $a, int $b)
    {
        $result = self::parse($input);
        Assert::assertEquals(new AnPlusB($a, $b), $result);
    }

    public function parseProvider()
    {
        // odd -> { a: 2, b: 1 }
        yield 'odd' => ['odd', 2, 1];
        yield 'odd + space' => [' odd ', 2, 1];
        // even -> { a: 2, b: 0 }
        yield 'even' => ['even', 2, 0];
        yield 'even + space' => [' even ', 2, 0];
        // <integer> -> { a: 0, b: integer.value }
        yield 'unsigned integer' => ['42', 0, 42];
        yield 'signed positive integer' => ['+42', 0, 42];
        yield 'signed negative integer' => ['-42', 0, -42];
        // <n-dimension> -> { a: dimension.value, b: 0}
        yield 'n-dimension' => ['3n', 3, 0];
        yield 'negative n-dimension' => ['-3n', -3, 0];
        yield 'n-dimension uppercase' => ['7N', 7, 0];
        // <n-dimension> <signed-integer> -> { a: dimension.value, b: integer.value }
        yield 'n-dimension w/ positive signed integer' => ['2n +3', 2, 3];
        yield 'n-dimension w/ negative signed integer' => ['2n -1', 2, -1];
        // <n-dimension> ['+' | '-'] <signless-integer> -> { a: dimension.value, b: integer.value }
        yield 'n-dimension "+" signless-integer' => ['4n + 3', 4, 3];
        yield 'n-dimension "-" signless-integer' => ['4n - 3', 4, -3];
        // <ndash-dimension> <signless-integer> -> { a: dimension.value, b: integer.value * -1 }
        yield 'ndash-dimension unsigned-integer' => ['3n- 12', 3, -12];
        // <ndashdigit-dimension> -> { a: dimension.value, b: dimension.unit }
        yield 'ndashdigit-dimension' => ['3n-42', 3, -42];
        // -n -> { a: -1: b: 0}
        yield '-n' => ['-n', -1, 0];
        // -n <signed-integer> -> { a: -1, b: integer.value }
        yield '-n w/ negative integer' => ['-n -2', -1, -2];
        yield '-n w/ signed positive integer' => ['-n +2', -1, 2];
        // -n ['+' | '-'] <signless-integer> -> { a: -1, b: integer.value * sign }
        yield '-n "+" unsigned-integer' => ['-n + 3', -1, 3];
        yield '-n "-" unsigned-integer' => ['-n - 3', -1, -3];
        // -n- <signless-integer> -> { a: -1, b: integer.value * -1 }
        yield '-n- unsigned-integer' => ['-n- 2', -1, -2];
        // <dashndashdigit-ident> -> { a: 1, b: identifier.value }
        yield 'dashndashdigit-ident' => ['-n-666', -1, -666];
    }

    /**
     * @dataProvider parseErrorsProvider
     */
    public function testParseErrors(string $input)
    {
        $this->expectException(ParseError::class);
        self::parse($input);
    }

    public function parseErrorsProvider()
    {
        yield 'unknown identifier' => ['food'];
        yield 'hash token' => ['#even'];
        yield 'a class' => ['.odd'];
        yield 'float' => ['1.3'];
        yield 'negative float' => ['-.33'];
        yield 'float dimension' => ['1.3n'];
        yield 'wrong unit dimension' => ['3x'];
        yield 'whitespace between leading + and n' => ['+ n-2'];
        yield 'spec invalid example #9' => ['3n + -6'];
        yield 'spec invalid example #10.1' => ['3 n'];
        yield 'spec invalid example #10.2' => ['+ 2n'];
        yield 'spec invalid example #10.3' => ['+ 2'];
    }

    /**
     * @dataProvider parseProvider
     */
    public function testParseWithRegexp(string $input, int $a, int $b)
    {
        $result = self::parseRegexp($input);
        Assert::assertEquals(new AnPlusB($a, $b), $result);
    }

    /**
     * @dataProvider parseErrorsProvider
     */
    public function testParseErrorsWithRegexp(string $input)
    {
        $this->expectException(ParseError::class);
        $result = self::parseRegexp($input);
    }

    private static function parseRegexp(string $input): AnPlusB
    {
        $tokenizer = new Tokenizer($input);
        $parser = new AnPlusBStringParser(new TokenStream($tokenizer, 1));
        return $parser->parse();
    }
}
