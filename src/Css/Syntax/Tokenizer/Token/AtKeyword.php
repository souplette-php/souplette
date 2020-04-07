<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Tokenizer\Token;

use JoliPotage\Css\Syntax\Tokenizer\TokenTypes;

final class AtKeyword extends CharacterToken
{
    public int $type = TokenTypes::AT_KEYWORD;

    public function __construct(string $value, int $position)
    {
        $this->representation = "@{$value}";
        // TODO: unescape value
        $this->value = $value;
        $this->position = $position;
    }
}
