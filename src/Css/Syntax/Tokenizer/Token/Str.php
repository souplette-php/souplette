<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Tokenizer\Token;

use JoliPotage\Css\Syntax\Tokenizer\TokenTypes;
use JoliPotage\Css\Syntax\Utils;

final class Str extends CharacterToken
{
    public int $type = TokenTypes::STRING;

    public function __construct(string $value, int $position)
    {
        $this->representation = sprintf('"%s"', $value);
        $this->value = Utils::unescapeString($value);
        $this->position = $position;
    }
}
