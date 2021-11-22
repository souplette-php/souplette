<?php declare(strict_types=1);

namespace Souplette\Html\Parser\Tokenizer\Token;

use Souplette\Html\Parser\Tokenizer\Token;
use Souplette\Html\Parser\Tokenizer\TokenType;

final class Comment extends Token
{
    const TYPE = TokenType::COMMENT;

    public function __construct(
        public string $data = '',
    ) {
    }
}
