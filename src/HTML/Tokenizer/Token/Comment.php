<?php declare(strict_types=1);

namespace Souplette\HTML\Tokenizer\Token;

use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\TokenType;

final class Comment extends Token
{
    const TYPE = TokenType::COMMENT;

    public function __construct(
        public string $data = '',
    ) {
    }
}
