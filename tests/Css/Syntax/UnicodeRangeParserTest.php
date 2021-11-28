<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Syntax;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Css\Syntax\Exception\ParseError;
use Souplette\Css\Syntax\Tokenizer\Tokenizer;
use Souplette\Css\Syntax\TokenStream\TokenStream;
use Souplette\Css\Syntax\UnicodeRangeParser;

final class UnicodeRangeParserTest extends TestCase
{
    /**
     * @dataProvider unicodeRangeProvider
     */
    public function testUnicodeRange(string $input, string $expected)
    {
        $tokens = new TokenStream(new Tokenizer($input), 1);
        $parser = new UnicodeRangeParser($tokens);
        $urange = $parser->parse();
        Assert::assertSame($expected, (string)$urange);
    }

    public function unicodeRangeProvider(): iterable
    {
        // First exercise all the clauses individually
        //<urange> = u '+' <ident-token> '?'* |
        // comments can go between tokens
        yield 'comments between tokens' => ["u/**/+/**/a/**/?", "U+A0-AF"];
        // capitalization doesn't matter
        yield 'case insensitive #0' => ["u+abc", "U+ABC"];
        yield 'case insensitive #1' => ["U+abc", "U+ABC"];
        yield 'case insensitive #2' => ["u+ABC", "U+ABC"];
        yield 'case insensitive #3' => ["U+ABC", "U+ABC"];
        yield 'case insensitive #4' => ["u+AbC", "U+ABC"];
        // 1-6 characters
        yield '1 character' => ["u+a", "U+A"];
        yield '2 characters' => ["u+aa", "U+AA"];
        yield '3 characters' => ["u+aaa", "U+AAA"];
        yield '4 characters' => ["u+aaaa", "U+AAAA"];
        yield '5 characters' => ["u+aaaaa", "U+AAAAA"];
        // Or ? at the end, still up to 6
        yield '1 <?> at end' => ["u+a?", "U+A0-AF"];
        yield '2 <?> at end' => ["u+a??", "U+A00-AFF"];
        yield '3 <?> at end' => ["u+a???", "U+A000-AFFF"];
        yield '4 <?> at end' => ["u+a????", "U+A0000-AFFFF"];
        //  u <dimension-token> '?'* |
        yield '<dimension> #1' => ["u/**/+0a/**/?", "U+A0-AF"];
        yield '<dimension> #2' => ["u+0a", "U+A"];
        yield '<dimension> #3' => ["U+0a0", "U+A0"];
        yield '<dimension> #4' => ["u+0aaaaa", "U+AAAAA"];
        yield '<dimension> #5' => ["u+0a0000", "U+A0000"];
        yield '<dimension> #6' => ["u+00000a", "U+A"];
        yield '<dimension> #7' => ["u+0a????", "U+A0000-AFFFF"];
        // Scinot!
        yield 'looks like scientific notation' => ["u+1e9a", "U+1E9A"];
        //  u <number-token> '?'* |
        yield '<number> #1' => ["u/**/+0/**/?", "U+0-F"];
        yield '<number> #2' => ["u+0", "U+0"];
        yield '<number> #3' => ["u+00", "U+0"];
        yield '<number> #4' => ["u+000", "U+0"];
        yield '<number> #5' => ["u+0000", "U+0"];
        yield '<number> #6' => ["u+00000", "U+0"];
        yield '<number> #7' => ["u+000000", "U+0"];
        yield '<number> #8' => ["u+00000?", "U+0-F"];
        yield '<number> #9' => ["u+0?????", "U+0-FFFFF"];
        // Scinot!
        yield '<number> looks like scientific notation #1' => ["u+1e3", "U+1E3"];
        yield '<number> looks like scientific notation #2' => ["u+1e-20", "U+1E-20"];
        //  u <number-token> <dimension-token> |
        yield '<number> <dimension> #1' => ["u/**/+0/**/-0a", "U+0-A"];
        yield '<number> <dimension> #2' => ["u+0-0a", "U+0-A"];
        yield '<number> <dimension> #3' => ["u+000000-0aaaaa", "U+0-AAAAA"];
        //  u <number-token> <number-token> |
        yield '<number> <number> #1' => ["u/**/+0/**/-1", "U+0-1"];
        yield '<number> <number> #2' => ["u+0-1", "U+0-1"];
        yield '<number> <number> #3' => ["u+000000-000001", "U+0-1"];
        //  u '+' '?'+
        yield '<+> <?>+ #1' => ["u/**/+/**/?", "U+0-F"];
        yield '<+> <?>+ #2' => ["u+?", "U+0-F"];
        yield '<+> <?>+ #3' => ["u+?????", "U+0-FFFFF"];
    }

    /**
     * @dataProvider invalidUnicodeRangeProvider
     */
    public function testInvalidUnicodeRange(string $input)
    {
        $this->expectException(ParseError::class);
        $tokens = new TokenStream(new Tokenizer($input), 1);
        $parser = new UnicodeRangeParser($tokens);
        $parser->parse();
    }

    public function invalidUnicodeRangeProvider(): iterable
    {
        // only hex
        yield ["u+efg"];
        // no spacing
        yield ["u+ abc"];
        yield ["u +abc"];
        yield ["u + abc"];
        yield ["U + a b c"];
        // 1-6 characters
        yield ["u+aaaaaaa"];
        // Or ? at the end, still up to 6
        yield ["u+aaaaaa?"];
        yield ["u+aaaaa??"];
        yield ["u+aaaa???"];
        yield ["u+aaa????"];
        yield ["u+aa?????"];
        yield ["u+a??????"];
        // no characters after ?
        yield ["u+a?a"];
        // Too large!
        yield ["u+aaaaaa"];
        yield ["u+a?????"];
        //  u <dimension-token> '?'* |
        yield ["u+0aaaaaa"];
        yield ["u+0a00000"];
        yield ["u+0aaaaa0"];
        yield ["u+00000aa"];
        yield ["u+00000a0"];
        yield ["u+000000a"];
        yield ["u+0a?????"];
        yield ["u+00a????"];
        // Too large!
        yield ["u+22222a"];
        //  u <number-token> '?'* |
        yield ["u/**/0"];        
        yield ["u+0000000"];
        yield ["u+0?a"];
        yield ["u+000000?"];
        yield ["u+00000??"];
        yield ["u+0??????"];
        // Too large!
        yield ["u+222222"];
        yield ["u+2?????"];
        //  u <number-token> <dimension-token> |
        yield ["u+0000000-0a"];
        yield ["u+0-0aaaaaa"];
        yield ["u+0-000000a"];
        yield ["u+0+0a"];
        yield ["u+0?-0a"];
        yield ["u+0-0a?"];
        // Too large!
        yield ["u+222222-22222a"];
        //  u <number-token> <number-token> |
        yield ["u-0-1"];
        yield ["u-0+1"];
        yield ["u+0+1"];
        yield ["u+0000000-1"];
        yield ["u+0-0000001"];
        // Too large!
        yield ["u+0-222222"];
        //  u '+' '?'+
        yield ["u+???????"];
        yield ["u+?a"];
        // U+FFFFFF is too large!
        yield ["u+??????"];
    }
}
