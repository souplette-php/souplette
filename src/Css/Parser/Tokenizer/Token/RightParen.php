<?php declare(strict_types=1);

namespace JoliPotage\Css\Parser\Tokenizer\Token;

use JoliPotage\Css\Parser\Tokenizer\TokenTypes;

final class RightParen extends SingleCharToken
{
    public int $type = TokenTypes::RPAREN;
    public string $value = ')';
    public string $representation = ')';
}
