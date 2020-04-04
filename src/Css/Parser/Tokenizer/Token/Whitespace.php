<?php declare(strict_types=1);

namespace JoliPotage\Css\Parser\Tokenizer\Token;

use JoliPotage\Css\Parser\Tokenizer\Token;
use JoliPotage\Css\Parser\Tokenizer\TokenTypes;

final class Whitespace extends Token
{
    public int $type = TokenTypes::WHITESPACE;

    public function __construct(int $position)
    {
        $this->position = $position;
    }
}
