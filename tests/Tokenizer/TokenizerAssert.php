<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\Tokenizer;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\Tokenizer;
use ju1ius\HtmlParser\Tokenizer\TokenizerStates;
use PHPUnit\Framework\Assert;

final class TokenizerAssert
{
    /**
     * @param string $input
     * @param Token[] $expectedTokens
     * @param array|null $expectedErrors
     * @param int $startState
     * @param bool $omitEOF
     */
    public static function tokensEquals(
        string $input,
        array $expectedTokens,
        ?array $expectedErrors = null,
        int $startState = TokenizerStates::DATA,
        bool $omitEOF = true
    ) {
        $tokenizer = new Tokenizer($input);
        $tokens = iterator_to_array($tokenizer->tokenize($startState));
        if ($omitEOF) {
            $eof = array_pop($tokens);
        }
        $expectedTokens = self::convertExpectedTokens($tokens);
        Assert::assertEquals($expectedTokens, $tokens);
        if ($expectedErrors) {
            Assert::assertEquals($expectedErrors, $tokenizer->getErrors());
        }
    }

    /**
     * @param array<Token|string> $tokens
     * @return Token[]
     */
    private static function convertExpectedTokens(array $tokens): array
    {
        return array_map(function($t) {
            return $t instanceof Token ? $t : Token::character($t);
        }, $tokens);
    }
}
