<?php declare(strict_types=1);

namespace JoliPotage\Css\Parser\Tokenizer\Token;

use JoliPotage\Css\Parser\Tokenizer\TokenTypes;

final class RightCurly extends SingleCharToken
{
    public int $type = TokenTypes::RCURLY;
    public string $value = '}';
    public string $representation = '}';
}
