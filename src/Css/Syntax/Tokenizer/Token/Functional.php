<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Tokenizer\Token;

use JoliPotage\Css\Syntax\Tokenizer\TokenTypes;

final class Functional extends CharacterToken
{
    public int $type = TokenTypes::FUNCTION;

    public function __construct(string $value, int $position)
    {
        $this->representation = "{$value}(";
        // TODO: unescape value
        $this->value = $value;
        $this->position = $position;
    }
}
