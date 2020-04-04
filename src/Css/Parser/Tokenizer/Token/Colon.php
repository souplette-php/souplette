<?php declare(strict_types=1);

namespace JoliPotage\Css\Parser\Tokenizer\Token;

use JoliPotage\Css\Parser\Tokenizer\TokenTypes;

final class Colon extends SingleCharToken
{
    public int $type = TokenTypes::COLON;
    public string $value = ':';
}
