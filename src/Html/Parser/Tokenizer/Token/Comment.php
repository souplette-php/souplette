<?php declare(strict_types=1);

namespace Souplette\Html\Parser\Tokenizer\Token;

use Souplette\Html\Parser\Tokenizer\Token;
use Souplette\Html\Parser\Tokenizer\TokenType;

final class Comment extends Token
{
    const TYPE = TokenType::COMMENT;
    public string $data = '';

    public function __construct(string $data = '')
    {
        $this->data = $data;
    }
}
