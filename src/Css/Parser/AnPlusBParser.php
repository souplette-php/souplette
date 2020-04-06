<?php declare(strict_types=1);

namespace JoliPotage\Css\Parser;

use JoliPotage\Css\CssOm\AnPlusB;
use JoliPotage\Css\Parser\Tokenizer\Token\Delimiter;
use JoliPotage\Css\Parser\Tokenizer\Token\Dimension;
use JoliPotage\Css\Parser\Tokenizer\Token\Identifier;
use JoliPotage\Css\Parser\Tokenizer\Token\Number;
use JoliPotage\Css\Parser\Tokenizer\TokenTypes;
use JoliPotage\Css\Parser\TokenStream\TokenStreamInterface;

final class AnPlusBParser
{
    private TokenStreamInterface $tokenStream;
    /**
     * @var int[]
     */
    private array $endTokenTypes;

    public function __construct(TokenStreamInterface $tokenStream, array $endTokenTypes = [TokenTypes::EOF])
    {
        $this->tokenStream = $tokenStream;
        $this->endTokenTypes = $endTokenTypes;
    }

    public function parse(): AnPlusB
    {
        $token = $this->tokenStream->skipWhitespace();
        $tt = $token->type;
        if ($tt === TokenTypes::IDENT) {
            $expr = $this->handleIdentifier($token);
        } elseif ($tt === TokenTypes::DIMENSION) {
            $expr = $this->handleDimension($token);
        } elseif ($tt === TokenTypes::NUMBER) {
            $expr = $this->handleNumber($token);
        } elseif ($tt === TokenTypes::DELIM) {
            $expr = $this->handleDelimiter($token);
        } else {
            throw $this->tokenStream->unexpectedToken($tt, TokenTypes::IDENT, TokenTypes::DIMENSION);
        }

        $this->tokenStream->consumeAndSkipWhitespace();
        $this->tokenStream->expect(...$this->endTokenTypes);

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
                throw $this->tokenStream->unexpectedToken($token->type, TokenTypes::NUMBER);
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
            throw $this->tokenStream->unexpectedToken($token->type, TokenTypes::NUMBER);
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
                throw $this->tokenStream->unexpectedToken($token->type, TokenTypes::NUMBER);
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
            throw $this->tokenStream->unexpectedToken($token->type, TokenTypes::NUMBER);
        }
        // <dashndashdigit-ident> -> { a: 1, b: identifier.value }
        // with the first two code points removed and the remainder interpreted as a base-10 number.
        // b is negative.
        if (preg_match('/^-n-(\d+)$/i', $token->value, $m)) {
            return new AnPlusB(-1, $m[1] * -1);
        }
        throw $this->tokenStream->unexpectedValue($token->value, 'odd', 'even', 'or a match for /-n[+-]\d+/i');
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
                throw $this->tokenStream->unexpectedToken($token->type, TokenTypes::DELIM);
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
            throw $this->tokenStream->unexpectedToken($nextToken->type, TokenTypes::NUMBER);
        }
        // <ndashdigit-dimension> -> { a: dimension.value, b: dimension.unit }
        // with the first code point removed and the remainder interpreted as a base-10 number.
        // B is negative.
        if ($token->isInteger && preg_match('/^n-(\d+)$/i', $token->unit, $m)) {
            return new AnPlusB($token->value, $m[1] * -1);
        }

        throw $this->tokenStream->unexpectedValue(
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
        throw $this->tokenStream->unexpectedValue((string)$token->value, 'an integer');
    }

    private function handleDelimiter(Delimiter $token): AnPlusB
    {
        if ($token->value !== '+') {
            throw $this->tokenStream->unexpectedValue($token->value, 'a "+" delimiter.');
        }
        $token = $this->tokenStream->consume();
        $this->tokenStream->expect(TokenTypes::IDENT);
        return $this->handleIdentifier($token);
    }

    private function isSignedInteger($token)
    {
        if ($token->type === TokenTypes::NUMBER && $token->isInteger) {
            return $token->representation[0] === '+' || $token->representation[0] === '-';
        }
        return false;
    }

    private function isSignlessInteger($token)
    {
        if ($token->type === TokenTypes::NUMBER && $token->isInteger) {
            return ctype_digit($token->representation[0]);
        }
        return false;
    }

    private function isSign($token)
    {
        return $token->type === TokenTypes::DELIM && ($token->value === '+' || $token->value === '-');
    }
}
