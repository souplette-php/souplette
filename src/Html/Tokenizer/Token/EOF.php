<?php declare(strict_types=1);

namespace Souplette\Html\Tokenizer\Token;

use Souplette\Html\Tokenizer\Token;
use Souplette\Html\Tokenizer\TokenType;

final class EOF extends Token
{
    const TYPE = TokenType::EOF;
}
