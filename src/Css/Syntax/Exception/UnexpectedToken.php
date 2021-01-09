<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Exception;

use Souplette\Css\Syntax\Tokenizer\Token;
use Souplette\Css\Syntax\Tokenizer\TokenTypes;

final class UnexpectedToken extends ParseError
{
    public static function expecting(Token $token, int $expectedType): self
    {
        return new self(sprintf(
            '%s@%d, expected %s',
            TokenTypes::nameOf($token->type),
            $token->position,
            TokenTypes::nameOf($expectedType),
        ));
    }

    public static function expectingOneOf(Token $token, int ...$expectedTypes): self
    {
        $expectedNames = array_map(fn($tt) => TokenTypes::nameOf($tt), $expectedTypes);
        return new self(sprintf(
            '%s@%d, expected %s',
            TokenTypes::nameOf($token->type),
            $token->position,
            implode(' | ', $expectedNames),
        ));
    }
}
