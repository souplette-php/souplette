<?php declare(strict_types=1);

namespace Souplette\Html\Tokenizer\Token;

use Souplette\Html\Tokenizer\TokenType;

final class StartTag extends Tag
{
    const TYPE = TokenType::START_TAG;
}
