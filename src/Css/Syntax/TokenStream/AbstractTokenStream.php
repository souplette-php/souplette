<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\TokenStream;

use JoliPotage\Css\Syntax\Exception\UnexpectedToken;
use JoliPotage\Css\Syntax\Exception\UnexpectedValue;
use JoliPotage\Css\Syntax\Tokenizer\BalancedPairs;
use JoliPotage\Css\Syntax\Tokenizer\Token;
use JoliPotage\Css\Syntax\Tokenizer\TokenTypes;

abstract class AbstractTokenStream implements TokenStreamInterface
{
    public function skipWhitespace(): Token
    {
        $token = $this->current();
        while ($token->type === TokenTypes::WHITESPACE) {
            $token = $this->consume();
        }
        return $token;
    }

    public function consumeAndSkipWhitespace(): Token
    {
        $this->consume();
        return $this->skipWhitespace();
    }

    public function consumeAnyValue(int $endTokenType)
    {
        $stack = new \SplStack();
        $stack->push($endTokenType);
        $tokens = [];
        while (true) {
            $token = $this->current();
            $tt = $token->type;
            if ($tt === TokenTypes::EOF) {
                break;
            }
            if ($tt === TokenTypes::BAD_URL || $tt === TokenTypes::BAD_STRING) {
                // TODO: parse error
                break;
            }
            if (isset(BalancedPairs::END_TOKENS[$tt]) && $tt !== $stack->top()) {
                // TODO: parse error: unbalanced closing token
                break;
            }
            if ($tt === $stack->top()) {
                $stack->pop();
                if ($stack->isEmpty()) {
                    break;
                }
            } elseif (isset(BalancedPairs::START_TOKENS[$tt])) {
                $stack->push(BalancedPairs::START_TOKENS[$tt]);
            }
            $tokens[] = $token;
            $this->consume();
        }
        return $tokens;
    }

    public function eat(int $tokenType): Token
    {
        $this->expect($tokenType);
        return $this->consume();
    }

    public function eatOneOf(int ...$tokenTypes): Token
    {
        $this->expectOneOf(...$tokenTypes);
        return $this->consume();
    }

    public function eatValue(int $tokenType, string $value): Token
    {
        $this->expectValue($tokenType, $value);
        return $this->consume();
    }

    public function expect(int $tokenType): Token
    {
        $token = $this->current();
        if ($token->type !== $tokenType) {
            throw UnexpectedToken::expecting($token, $tokenType);
        }
        return $token;
    }

    public function expectOneOf(int ...$tokenTypes): Token
    {
        $token = $this->current();
        if (!in_array($token->type, $tokenTypes, true)) {
            throw UnexpectedToken::expectingOneOf($token, ...$tokenTypes);
        }
        return $token;
    }

    public function expectValue(int $tokenType, string $value)
    {
        $token = $this->current();
        if ($token->type !== $tokenType) {
            throw UnexpectedToken::expecting($token, $tokenType);
        }
        if ($token->value !== $value) {
            throw UnexpectedValue::expecting($token->value, $value);
        }
    }
}