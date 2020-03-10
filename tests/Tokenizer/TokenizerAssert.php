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
     */
    public static function tokensEquals(
        string $input,
        array $expectedTokens,
        ?array $expectedErrors = null,
        int $startState = TokenizerStates::DATA
    ) {
        $tokenizer = new Tokenizer($input);
        $tokens = iterator_to_array($tokenizer->tokenize($startState));
        Assert::assertEquals($expectedTokens, $tokens);
        if ($expectedErrors) {
            Assert::assertEquals($expectedErrors, $tokenizer->getErrors());
        }
    }
}
