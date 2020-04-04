<?php declare(strict_types=1);

namespace JoliPotage\Css\Parser\Tokenizer\Token;

use JoliPotage\Css\Parser\Tokenizer\TokenTypes;

final class BadString extends CharacterToken
{
    public int $type = TokenTypes::BAD_STRING;
}
