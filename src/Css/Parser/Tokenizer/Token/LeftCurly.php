<?php declare(strict_types=1);

namespace JoliPotage\Css\Parser\Tokenizer\Token;

use JoliPotage\Css\Parser\Tokenizer\TokenTypes;

final class LeftCurly extends SingleCharToken
{
    public int $type = TokenTypes::LCURLY;
    public string $value = '{';
}
