<?php declare(strict_types=1);

namespace Souplette\Html\Tokenizer\Token;

use Souplette\Html\Tokenizer\TokenType;

final class EndTag extends Tag
{
    const TYPE = TokenType::END_TAG;
}
