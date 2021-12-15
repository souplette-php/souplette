<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\Tokenizer\Token;

use Souplette\CSS\Syntax\Tokenizer\TokenType;

final class BadUrl extends CharacterToken
{
    const TYPE = TokenType::BAD_URL;
}
