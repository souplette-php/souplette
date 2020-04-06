<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Tokenizer\Token;

use JoliPotage\Css\Syntax\Tokenizer\TokenTypes;

final class AtKeyword extends CharacterToken
{
    public int $type = TokenTypes::AT_KEYWORD;
}
