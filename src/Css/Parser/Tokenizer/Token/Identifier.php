<?php declare(strict_types=1);

namespace JoliPotage\Css\Parser\Tokenizer\Token;

use JoliPotage\Css\Parser\Tokenizer\TokenTypes;

final class Identifier extends CharacterToken
{
    public int $type = TokenTypes::IDENT;
}
