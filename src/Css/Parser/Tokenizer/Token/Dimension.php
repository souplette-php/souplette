<?php declare(strict_types=1);

namespace JoliPotage\Css\Parser\Tokenizer\Token;

use JoliPotage\Css\Parser\Tokenizer\TokenTypes;

final class Dimension extends NumericToken
{
    public int $type = TokenTypes::DIMENSION;
    public string $unit;
}
