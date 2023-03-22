<?php declare(strict_types=1);

namespace Souplette\HTML\Tokenizer\Token;

use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\TokenKind;

final class EOF extends Token
{
    const KIND = TokenKind::EOF;
}
