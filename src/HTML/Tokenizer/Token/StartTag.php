<?php declare(strict_types=1);

namespace Souplette\HTML\Tokenizer\Token;

use Souplette\HTML\Tokenizer\TokenKind;

final class StartTag extends Tag
{
    const KIND = TokenKind::StartTag;
}
