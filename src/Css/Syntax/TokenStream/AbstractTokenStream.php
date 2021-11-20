<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\TokenStream;

use Souplette\Css\Syntax\Exception\UnexpectedToken;
use Souplette\Css\Syntax\Exception\UnexpectedValue;
use Souplette\Css\Syntax\Tokenizer\BalancedPairs;
use Souplette\Css\Syntax\Tokenizer\Token;
use Souplette\Css\Syntax\Tokenizer\TokenType;

abstract class AbstractTokenStream implements TokenStreamInterface
{
    public function skipWhitespace(): Token
    {
        $token = $this->current();
        while ($token::TYPE === TokenType::WHITESPACE) {
            $token = $this->consume();
        }
        return $token;
    }

    public function consumeAndSkipWhitespace(): Token
    {
        $this->consume();
        return $this->skipWhitespace();
    }

    public function consumeAnyValue(TokenType $endTokenType): array
    {
        $stack = new \SplStack();
        $stack->push($endTokenType);
        $tokens = [];
        while (true) {
            $token = $this->current();
            $tt = $token::TYPE;
            if ($tt === TokenType::EOF) {
                break;
            }
            if ($tt === TokenType::BAD_URL || $tt === TokenType::BAD_STRING) {
                // TODO: parse error
                break;
            }
            if (BalancedPairs::isEndToken($tt) && $tt !== $stack->top()) {
                // TODO: parse error: unbalanced closing token
                break;
            }
            if ($tt === $stack->top()) {
                $stack->pop();
                if ($stack->isEmpty()) {
                    break;
                }
            } else if ($ett = BalancedPairs::getEndTokenType($tt)) {
                $stack->push($ett);
            }
            $tokens[] = $token;
            $this->consume();
        }
        return $tokens;
    }

    public function eat(TokenType $tokenType): Token
    {
        $this->expect($tokenType);
        return $this->consume();
    }

    public function eatOneOf(TokenType ...$tokenTypes): Token
    {
        $this->expectOneOf(...$tokenTypes);
        return $this->consume();
    }

    public function eatValue(TokenType $tokenType, string $value): Token
    {
        $this->expectValue($tokenType, $value);
        return $this->consume();
    }

    public function expect(TokenType $tokenType): Token
    {
        $token = $this->current();
        if ($token::TYPE !== $tokenType) {
            throw UnexpectedToken::expecting($token, $tokenType);
        }
        return $token;
    }

    public function expectOneOf(TokenType ...$tokenTypes): Token
    {
        $token = $this->current();
        if (!in_array($token::TYPE, $tokenTypes, true)) {
            throw UnexpectedToken::expectingOneOf($token, ...$tokenTypes);
        }
        return $token;
    }

    public function expectValue(TokenType $tokenType, string $value)
    {
        $token = $this->current();
        if ($token::TYPE !== $tokenType) {
            throw UnexpectedToken::expecting($token, $tokenType);
        }
        if ($token->value !== $value) {
            throw UnexpectedValue::expecting($token->value, $value);
        }
    }
}
