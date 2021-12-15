<?php declare(strict_types=1);

namespace Souplette\HTML\Tokenizer\Token;

use Souplette\HTML\Tokenizer\TokenType;

final class StartTag extends Tag
{
    const TYPE = TokenType::START_TAG;
}
