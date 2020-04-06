<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Tokenizer\Token;

use JoliPotage\Css\Syntax\Tokenizer\Token;
use JoliPotage\Css\Syntax\Tokenizer\TokenTypes;

final class Delimiter extends Token
{
    public int $type = TokenTypes::DELIM;
    public string $value;

    public function __construct(string $value, int $position)
    {
        $this->representation = $value;
        $this->value = $value;
        $this->position = $position;
    }
}
