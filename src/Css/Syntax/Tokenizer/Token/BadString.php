<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Tokenizer\Token;

use JoliPotage\Css\Syntax\Tokenizer\TokenTypes;

final class BadString extends CharacterToken
{
    public int $type = TokenTypes::BAD_STRING;
}
