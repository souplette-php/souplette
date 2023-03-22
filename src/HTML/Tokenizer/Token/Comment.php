<?php declare(strict_types=1);

namespace Souplette\HTML\Tokenizer\Token;

use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\TokenKind;

final class Comment extends Token
{
    const KIND = TokenKind::Comment;

    public function __construct(
        public string $data = '',
    ) {
    }
}
