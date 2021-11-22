<?php declare(strict_types=1);

namespace Souplette\Html\Tokenizer\Token;

use Souplette\Html\Tokenizer\Token;
use Souplette\Html\Tokenizer\TokenType;

final class Comment extends Token
{
    const TYPE = TokenType::COMMENT;

    public function __construct(
        public string $data = '',
    ) {
    }
}
