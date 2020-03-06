<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\Tokenizer;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\Tokenizer;
use PHPUnit\Framework\Assert;

final class TokenizerAssert
{
    /**
     * @param string $input
     * @param Token[] $expected
     */
    public static function tokensEquals(string $input, array $expected)
    {
        $tokenizer = new Tokenizer($input);
        $actual = iterator_to_array($tokenizer->tokenize());
        Assert::assertEquals($expected, $actual);
    }
}
