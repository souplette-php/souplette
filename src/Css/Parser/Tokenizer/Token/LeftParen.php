<?php declare(strict_types=1);

namespace JoliPotage\Css\Parser\Tokenizer\Token;

use JoliPotage\Css\Parser\Tokenizer\TokenTypes;

final class LeftParen extends SingleCharToken
{
    public int $type = TokenTypes::LPAREN;
    public string $value = '(';
}
