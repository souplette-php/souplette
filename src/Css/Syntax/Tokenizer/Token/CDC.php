<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Tokenizer\Token;

use JoliPotage\Css\Syntax\Tokenizer\Token;
use JoliPotage\Css\Syntax\Tokenizer\TokenTypes;

final class CDC extends Token
{
    public int $type = TokenTypes::CDC;
    public string $representation = '-->';

    public function __construct(int $pos)
    {
        $this->position = $pos;
    }
}
