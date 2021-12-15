<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\Tokenizer;

final class BalancedPairs
{
    public static function getEndTokenType(TokenType $tt): ?TokenType
    {
        return match ($tt) {
            TokenType::LCURLY => TokenType::RCURLY,
            TokenType::LBRACK => TokenType::RBRACK,
            TokenType::LPAREN, TokenType::URL, TokenType::FUNCTION => TokenType::RPAREN,
            default => null,
        };
    }

    public static function isEndToken(TokenType $tt): bool
    {
        return match($tt) {
            TokenType::RCURLY, TokenType::RBRACK, TokenType::RPAREN => true,
            default => false,
        };
    }
}
