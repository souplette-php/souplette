<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Exception;

use Souplette\Css\Syntax\Tokenizer\Token;
use Souplette\Css\Syntax\Tokenizer\TokenType;

final class UnexpectedToken extends ParseError
{
    public static function expecting(Token $token, TokenType $expectedType): self
    {
        return new self(sprintf(
            '%s@%d, expected %s',
            $token::TYPE->name,
            $token->position,
            $expectedType->name,
        ));
    }

    public static function expectingOneOf(Token $token, TokenType ...$expectedTypes): self
    {
        $expectedNames = array_map(fn(TokenType $tt) => $tt->name, $expectedTypes);
        return new self(sprintf(
            '%s@%d, expected %s',
            $token::TYPE->name,
            $token->position,
            implode(' | ', $expectedNames),
        ));
    }
}
