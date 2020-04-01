<?php declare(strict_types=1);

namespace JoliPotage\Html\Parser\Tokenizer\Token;

use JoliPotage\Html\Parser\Tokenizer\Token;
use JoliPotage\Html\Parser\Tokenizer\TokenTypes;

final class Character extends Token
{
    public int $type = TokenTypes::CHARACTER;
    public string $data;

    public function __construct(string $data = '')
    {
        $this->data = $data;
    }
}
