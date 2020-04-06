<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Tokenizer\Token;

use JoliPotage\Css\Syntax\Tokenizer\TokenTypes;

final class RightBracket extends SingleCharToken
{
    public int $type = TokenTypes::RBRACK;
    public string $value = ']';
    public string $representation = ']';
}
