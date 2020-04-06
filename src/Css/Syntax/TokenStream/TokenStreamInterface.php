<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\TokenStream;

use JoliPotage\Css\Syntax\Exception\UnexpectedToken;
use JoliPotage\Css\Syntax\Tokenizer\Token;

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
     * @param int $endTokenType
     * @return Token[]
     */
    public function consumeAnyValue(int $endTokenType);

    /**
     * Ensures the current token is of the given type, then consumes the next token and return it.
     *
     * @param int $tokenType
     * @return Token
     */
    public function eat(int $tokenType): Token;

    /**
     * Ensures the current token is one of the given types, then consumes the next token and return it.
     *
     * @param int ...$tokenTypes
     * @return Token
     */
    public function eatOneOf(int ...$tokenTypes): Token;

    /**
     * Ensures the current token is of the given type and has the given value,
     * then consumes the next token and return it.
     *
     * @param int $tokenType
     * @param string $value
     * @return Token
     */
    public function eatValue(int $tokenType, string $value): Token;

    /**
     * Ensures the current token is of the given type, then returns the current token.
     *
     * @param int $tokenType
     * @return Token
     */
    public function expect(int $tokenType): Token;

    /**
     * Ensures the current token is one of the given types, then returns the current token.
     *
     * @param int ...$tokenTypes
     * @return Token
     */
    public function expectOneOf(int ...$tokenTypes): Token;

    /**
     * Ensures the current token is of the given type and has the given value,
     * then returns the current token.
     *
     * @param int $tokenType
     * @param string $value
     * @return mixed
     */
    public function expectValue(int $tokenType, string $value);

    /**
     * Creates an `UnexpectedToken` exception.
     *
     * @param int $tokenType
     * @param int ...$expected
     * @return UnexpectedToken
     */
    public function unexpectedToken(int $tokenType, int ...$expected): UnexpectedToken;

    public function unexpectedValue(string $value, string ...$expected): UnexpectedToken;
}
