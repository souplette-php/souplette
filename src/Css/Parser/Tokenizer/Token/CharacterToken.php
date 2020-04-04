<?php declare(strict_types=1);

namespace JoliPotage\Css\Parser\Tokenizer\Token;

use JoliPotage\Css\Parser\Tokenizer\Token;

abstract class CharacterToken extends Token
{
    public string $value;

    public function __construct(string $value, int $position)
    {
        $this->value = $value;
        $this->position = $position;
    }
}
