<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Tokenizer\Token;

use JoliPotage\Css\Syntax\Tokenizer\TokenTypes;

final class Hash extends CharacterToken
{
    public int $type = TokenTypes::HASH;
    public bool $isId = false;
}
