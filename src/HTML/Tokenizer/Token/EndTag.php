<?php declare(strict_types=1);

namespace Souplette\HTML\Tokenizer\Token;

use Souplette\HTML\Tokenizer\TokenType;

final class EndTag extends Tag
{
    const TYPE = TokenType::END_TAG;
}
