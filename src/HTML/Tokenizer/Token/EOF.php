<?php declare(strict_types=1);

namespace Souplette\HTML\Tokenizer\Token;

use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\TokenType;

final class EOF extends Token
{
    const TYPE = TokenType::EOF;
}
