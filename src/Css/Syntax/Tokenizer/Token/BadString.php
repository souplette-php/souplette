<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Tokenizer\Token;

use Souplette\Css\Syntax\Tokenizer\TokenType;

final class BadString extends CharacterToken
{
    const TYPE = TokenType::BAD_STRING;
}
