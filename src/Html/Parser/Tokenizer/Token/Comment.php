<?php declare(strict_types=1);

namespace JoliPotage\Html\Parser\Tokenizer\Token;

use JoliPotage\Html\Parser\Tokenizer\Token;
use JoliPotage\Html\Parser\Tokenizer\TokenTypes;

final class Comment extends Token
{
    public int $type = TokenTypes::COMMENT;
    public string $data = '';

    public function __construct(string $data = '')
    {
        $this->data = $data;
    }
}
