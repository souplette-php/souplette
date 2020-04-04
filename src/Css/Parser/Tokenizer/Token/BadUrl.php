<?php declare(strict_types=1);

namespace JoliPotage\Css\Parser\Tokenizer\Token;

use JoliPotage\Css\Parser\Tokenizer\TokenTypes;

final class BadUrl extends CharacterToken
{
    public int $type = TokenTypes::BAD_URL;
}
