<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\TokenStream;

use Souplette\Css\Syntax\Tokenizer\Token;
use Souplette\Css\Syntax\Tokenizer\TokenType;

interface TokenStreamInterface
{
    /**
     * Returns the current token in the stream.
     *
     * @return Token
     */
    public function current(): Token;

    /**
     * Consumes $n token from the stream and returns the new current token.
     *
     * @param int $n
     * @return Token
     */
    public function consume(int $n = 1): Token;

    /**
     * Returns the token $n tokens ahead of the current token without consuming anything.
     *
     * @param int $offset
     * @return Token
     */
    public function lookahead(int $offset = 1): Token;

    /**
     * Skips whitespace and returns the current token.
     * @return Token
     */
    public function skipWhitespace(): Token;

    /**
     * Consumes the current token, skips whitespace and returns the current token.
     * @return Token
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
     *
     * @param TokenType $tokenType
     * @return Token
     */
    public function eat(TokenType $tokenType): Token;

    /**
     * Ensures the current token is one of the given types, then consumes the next token and return it.
     *
     * @param TokenType ...$tokenTypes
     * @return Token
     */
    public function eatOneOf(TokenType ...$tokenTypes): Token;

    /**
     * Ensures the current token is of the given type and has the given value,
     * then consumes the next token and return it.
     *
     * @param TokenType $tokenType
     * @param string $value
     * @return Token
     */
    public function eatValue(TokenType $tokenType, string $value): Token;

    /**
     * Ensures the current token is of the given type, then returns the current token.
     *
     * @param TokenType $tokenType
     * @return Token
     */
    public function expect(TokenType $tokenType): Token;

    /**
     * Ensures the current token is one of the given types, then returns the current token.
     *
     * @param TokenType ...$tokenTypes
     * @return Token
     */
    public function expectOneOf(TokenType ...$tokenTypes): Token;

    /**
     * Ensures the current token is of the given type and has the given value,
     * then returns the current token.
     *
     * @param TokenType $tokenType
     * @param string $value
     * @return mixed
     */
    public function expectValue(TokenType $tokenType, string $value);
}
