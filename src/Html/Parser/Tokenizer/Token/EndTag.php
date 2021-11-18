<?php declare(strict_types=1);

namespace Souplette\Html\Parser\Tokenizer\Token;

use Souplette\Html\Parser\Tokenizer\TokenType;

final class EndTag extends Tag
{
    const TYPE = TokenType::END_TAG;
}
