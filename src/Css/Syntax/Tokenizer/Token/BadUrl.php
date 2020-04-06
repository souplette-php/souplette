<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Tokenizer\Token;

use JoliPotage\Css\Syntax\Tokenizer\TokenTypes;

final class BadUrl extends CharacterToken
{
    public int $type = TokenTypes::BAD_URL;
}
