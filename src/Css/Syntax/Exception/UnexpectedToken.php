<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Exception;

use JoliPotage\Css\Syntax\Tokenizer\Token;
use JoliPotage\Css\Syntax\Tokenizer\TokenTypes;

final class UnexpectedToken extends ParseError
{
    public static function expecting(Token $token, int $expectedType): self
    {
        return new self(sprintf(
            'Expected %s but got %s',
            TokenTypes::nameOf($expectedType),
            TokenTypes::nameOf($token->type)
        ));
    }

    public static function expectingOneOf(Token $token, int ...$expectedTypes): self
    {
        $expectedNames = array_map(fn($tt) => TokenTypes::nameOf($tt), $expectedTypes);
        return new self(sprintf(
            'Expected %s but got %s',
            implode(' | ', $expectedNames),
            TokenTypes::nameOf($token->type)
        ));
    }
}
