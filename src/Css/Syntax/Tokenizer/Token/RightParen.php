<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Tokenizer\Token;

use JoliPotage\Css\Syntax\Tokenizer\TokenTypes;

final class RightParen extends SingleCharToken
{
    public int $type = TokenTypes::RPAREN;
    public string $value = ')';
    public string $representation = ')';
}
