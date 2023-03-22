<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\TokenStream;

use Souplette\CSS\Syntax\Tokenizer\Token;
use Souplette\CSS\Syntax\Tokenizer\TokenType;

interface TokenStreamInterface
{
    /**
     * Returns the current token in the stream.
     */
    public function current(): Token;

    /**
     * Consumes $n token from the stream and returns the new current token.
     */
    public function consume(int $n = 1): Token;

    /**
     * Returns the token $n tokens ahead of the current token without consuming anything.
     */
    public function lookahead(int $offset = 1): Token;

    /**
     * Skips whitespace and returns the current token.
     */
    public function skipWhitespace(): Token;

    /**
     * Consumes the current token, skips whitespace and returns the current token.
     */
    public function consumeAndSkipWhitespace(): Token;

    /**
     * @see https://www.w3.org/TR/css-syntax-3/#typedef-any-value
     * @param TokenType $endTokenType
     * @return Token[]
     */
    public function consumeAnyValue(TokenType $endTokenType): array;

    /**
     * Ensures the current token is of the given type, then consumes the next token and return it.
     */
    public function eat(TokenType $tokenType): Token;

    /**
     * Ensures the current token is one of the given types, then consumes the next token and return it.
     */
    public function eatOneOf(TokenType ...$tokenTypes): Token;

    /**
     * Ensures the current token is of the given type and has the given value,
     * then consumes the next token and return it.
     */
    public function eatValue(TokenType $tokenType, string $value): Token;

    /**
     * Ensures the current token is of the given type, then returns the current token.
     */
    public function expect(TokenType $tokenType): Token;

    /**
     * Ensures the current token is one of the given types, then returns the current token.
     */
    public function expectOneOf(TokenType ...$tokenTypes): Token;

    /**
     * Ensures the current token is of the given type and has the given value,
     * then returns the current token.
     */
    public function expectValue(TokenType $tokenType, string $value): Token;
}
