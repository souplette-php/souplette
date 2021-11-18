<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Tokenizer\Token;

use Souplette\Css\Syntax\Tokenizer\TokenType;

final class Url extends CharacterToken
{
    const TYPE = TokenType::URL;
}
