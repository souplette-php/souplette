<?php declare(strict_types=1);

namespace Souplette\Css\Syntax;

use Souplette\Css\Syntax\Exception\UnexpectedToken;
use Souplette\Css\Syntax\Exception\UnexpectedValue;
use Souplette\Css\Syntax\Node\AnPlusB;
use Souplette\Css\Syntax\Tokenizer\Token\Delimiter;
use Souplette\Css\Syntax\Tokenizer\Token\Dimension;
use Souplette\Css\Syntax\Tokenizer\Token\Identifier;
use Souplette\Css\Syntax\Tokenizer\Token\Number;
use Souplette\Css\Syntax\Tokenizer\TokenType;
use Souplette\Css\Syntax\TokenStream\TokenStreamInterface;

/**
 * @see https://www.w3.org/TR/css-syntax-3/#anb-microsyntax
 */
final class AnPlusBParser
{
    private TokenStreamInterface $tokenStream;
    /**
     * @var int[]
     */
    private array $endTokenTypes;

    public function __construct(TokenStreamInterface $tokenStream, array $endTokenTypes = [TokenType::EOF])
    {
        $this->tokenStream = $tokenStream;
        $this->endTokenTypes = $endTokenTypes;
    }

    public function parse(): AnPlusB
    {
        $token = $this->tokenStream->skipWhitespace();
        $tt = $token::TYPE;
        if ($tt === TokenType::IDENT) {
            $expr = $this->handleIdentifier($token);
        } else if ($tt === TokenType::DIMENSION) {
            $expr = $this->handleDimension($token);
        } else if ($tt === TokenType::NUMBER) {
            $expr = $this->handleNumber($token);
        } else if ($tt === TokenType::DELIM) {
            $expr = $this->handleDelimiter($token);
        } else {
            throw UnexpectedToken::expectingOneOf($token, TokenType::NUMBER, TokenType::IDENT, TokenType::DIMENSION);
        }

        $this->tokenStream->consumeAndSkipWhitespace();
        $this->tokenStream->expectOneOf(...$this->endTokenTypes);

        return $expr;
    }
    /*
     * ~ odd | even | -n (\s* [+-] \s* \d+)? ~xi
     */
    private function handleIdentifier(Identifier $token): AnPlusB
    {
        $identifier = strtolower($token->value);
        if ($identifier === 'odd') {
            return new AnPlusB(2, 1);
        }
        if ($identifier === 'even') {
            return new AnPlusB(2, 0);
        }
        if ($identifier === 'n') {
            $token = $this->tokenStream->consumeAndSkipWhitespace();
            // '+'?† n <signed-integer> -> { a: 1, b: integer.value }
            if ($this->isSignedInteger($token)) {
                return new AnPlusB(1, $token->value);
            }
            // '+'?† n ['+' | '-'] <signless-integer> -> { a: 1, b: integer.value }
            if ($this->isSign($token)) {
                // If a '-' was provided between the two, B is instead the negation of the integer’s value.
                $sign = $token->representation[0] === '-' ? -1 : 1;
                $token = $this->tokenStream->consumeAndSkipWhitespace();
                if ($this->isSignlessInteger($token)) {
                    return new AnPlusB(1, $token->value * $sign);
                }
                throw UnexpectedToken::expecting($token, TokenType::NUMBER);
            }
            // '+'?† n -> { a: 1, b: 0 }
            return new AnPlusB(1, 0);
        }
        if ($identifier === 'n-') {
            $token = $this->tokenStream->consumeAndSkipWhitespace();
            // '+'?† n- <signless-integer> -> { a: 1, b: integer.value * -1 }
            if ($this->isSignlessInteger($token)) {
                return new AnPlusB(1, $token->value * -1);
            }
            throw UnexpectedToken::expecting($token, TokenType::NUMBER);
        }
        // '+'?† <ndashdigit-ident> -> { a: 1, b: <identifier.value>}
        // with the first code point removed and the remainder interpreted as a base-10 number.
        // B is negative.
        if (preg_match('/^n-(\d+)$/', $identifier, $m)) {
           return new AnPlusB(1, $m[1] * -1);
        }

        if ($identifier === '-n') {
            $token = $this->tokenStream->consumeAndSkipWhitespace();
            // -n <signed-integer> -> { a: -1, b: integer.value }
            if ($this->isSignedInteger($token)) {
                return new AnPlusB(-1, $token->value);
            }
            // -n ["+" | "-"] <signless-integer> -> { a: -1, b: integer.value }
            if ($this->isSign($token)) {
                // If a '-' was provided between the two, B is instead the negation of the integer’s value.
                $sign = $token->representation[0] === '-' ? -1 : 1;
                $token = $this->tokenStream->consumeAndSkipWhitespace();
                if ($this->isSignlessInteger($token)) {
                    return new AnPlusB(-1, $token->value * $sign);
                }
                throw UnexpectedToken::expecting($token, TokenType::NUMBER);
            }
            // -n -> { a: -1: b: 0 }
            return new AnPlusB(-1, 0);
        }
        // -n- <signless-integer> -> { a: -1, b: integer.value * -1 }
        if ($identifier === '-n-') {
            $token = $this->tokenStream->consumeAndSkipWhitespace();
            if ($this->isSignlessInteger($token)) {
                return new AnPlusB(-1, $token->value * -1);
            }
            throw UnexpectedToken::expecting($token, TokenType::NUMBER);
        }
        // <dashndashdigit-ident> -> { a: 1, b: identifier.value }
        // with the first two code points removed and the remainder interpreted as a base-10 number.
        // b is negative.
        if (preg_match('/^-n-(\d+)$/i', $token->value, $m)) {
            return new AnPlusB(-1, $m[1] * -1);
        }
        throw UnexpectedValue::expecting($token->value, 'odd, even, or a match for /-n[+-]\d+/i');
    }

    private function handleDimension(Dimension $token): AnPlusB
    {
        if ($token->isInteger && strcasecmp($token->unit, 'n') === 0) {
            $nextToken = $this->tokenStream->consumeAndSkipWhitespace();
            // <n-dimension> <signed-integer> -> { a: dimension.value, b: integer.value }
            if ($this->isSignedInteger($nextToken)) {
                return new AnPlusB($token->value, $nextToken->value);
            }
            // <n-dimension> ['+' | '-'] <signless-integer> -> { a: dimension.value, b: integer.value }
            // If a '-' was provided between the two, B is instead the negation of the integer’s value.
            if ($this->isSign($nextToken)) {
                $sign = $nextToken->representation[0] === '-' ? -1 : 1;
                $nextToken = $this->tokenStream->consumeAndSkipWhitespace();
                if ($this->isSignlessInteger($nextToken)) {
                    return new AnPlusB($token->value, $nextToken->value * $sign);
                }
                throw UnexpectedToken::expecting($token, TokenType::DELIM);
            }
            // <n-dimension> -> { a: dimension.value, b: 0 }
            return new AnPlusB($token->value, 0);
        }
        // <ndash-dimension> <signless-integer> -> { a: dimension.value, b: integer.value * -1 }
        if ($token->isInteger && strcasecmp($token->unit, 'n-') === 0) {
            $nextToken = $this->tokenStream->consumeAndSkipWhitespace();
            if ($this->isSignlessInteger($nextToken)) {
                return new AnPlusB($token->value, $nextToken->value * -1);
            }
            throw UnexpectedToken::expecting($nextToken, TokenType::NUMBER);
        }
        // <ndashdigit-dimension> -> { a: dimension.value, b: dimension.unit }
        // with the first code point removed and the remainder interpreted as a base-10 number.
        // B is negative.
        if ($token->isInteger && preg_match('/^n-(\d+)$/i', $token->unit, $m)) {
            return new AnPlusB($token->value, $m[1] * -1);
        }

        throw UnexpectedValue::expecting(
            sprintf('%s%s', $token->value, $token->unit),
            'a valid dimension'
        );
    }

    private function handleNumber(Number $token): AnPlusB
    {
        // <integer> -> { a: 0, b: integer.value }
        if ($token->isInteger) {
            return new AnPlusB(0, $token->value);
        }
        throw UnexpectedValue::expecting((string)$token->value, 'an integer');
    }

    private function handleDelimiter(Delimiter $token): AnPlusB
    {
        if ($token->value !== '+') {
            throw UnexpectedValue::expecting($token->value, 'a "+" delimiter.');
        }
        $token = $this->tokenStream->consume();
        $this->tokenStream->expect(TokenType::IDENT);
        return $this->handleIdentifier($token);
    }

    private function isSignedInteger($token)
    {
        if ($token::TYPE === TokenType::NUMBER && $token->isInteger) {
            return $token->representation[0] === '+' || $token->representation[0] === '-';
        }
        return false;
    }

    private function isSignlessInteger($token)
    {
        if ($token::TYPE === TokenType::NUMBER && $token->isInteger) {
            return ctype_digit($token->representation[0]);
        }
        return false;
    }

    private function isSign($token)
    {
        return $token::TYPE === TokenType::DELIM && ($token->value === '+' || $token->value === '-');
    }
}
