<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Tokenizer\Token;

use JoliPotage\Css\Syntax\Tokenizer\Token;
use JoliPotage\Css\Syntax\Tokenizer\TokenTypes;

final class Whitespace extends Token
{
    public int $type = TokenTypes::WHITESPACE;
    public string $representation = ' ';

    public function __construct(int $position)
    {
        $this->position = $position;
    }
}
