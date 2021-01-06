<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Tokenizer\Token;

use JoliPotage\Css\Syntax\Tokenizer\TokenTypes;

final class Hash extends CharacterToken
{
    public int $type = TokenTypes::HASH;
    public bool $isId;

    public function __construct(string $value, int $position, bool $isId = false)
    {
        $this->representation = "#{$value}";
        // TODO: unescape value
        $this->value = $value;
        $this->position = $position;
        $this->isId = $isId;
    }
}