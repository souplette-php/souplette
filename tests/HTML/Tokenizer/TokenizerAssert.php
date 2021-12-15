<?php declare(strict_types=1);

namespace Souplette\Tests\HTML\Tokenizer;

use PHPUnit\Framework\Assert;
use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\Tokenizer;
use Souplette\HTML\Tokenizer\TokenizerState;

final class TokenizerAssert
{
    /**
     * @param string $input
     * @param Token[] $expectedTokens
     * @param array|null $expectedErrors
     * @param TokenizerState $startState
     * @param bool $omitEOF
     */
    public static function tokensEquals(
        string $input,
        array $expectedTokens,
        ?array $expectedErrors = null,
        TokenizerState $startState = TokenizerState::DATA,
        bool $omitEOF = true
    ) {
        $tokenizer = new Tokenizer($input);
        $tokens = iterator_to_array($tokenizer->tokenize($startState), false);
        if ($omitEOF) {
            $eof = array_pop($tokens);
        }
        $expectedTokens = self::convertExpectedTokens($expectedTokens);
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
