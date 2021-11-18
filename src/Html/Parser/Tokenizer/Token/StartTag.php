<?php declare(strict_types=1);

namespace Souplette\Html\Parser\Tokenizer\Token;

use Souplette\Html\Parser\Tokenizer\TokenType;

final class StartTag extends Tag
{
    const TYPE = TokenType::START_TAG;
}
