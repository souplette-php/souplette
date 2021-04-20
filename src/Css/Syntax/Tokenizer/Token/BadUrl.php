<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Tokenizer\Token;

use Souplette\Css\Syntax\Tokenizer\TokenTypes;

final class BadUrl extends CharacterToken
{
    const TYPE = TokenTypes::BAD_URL;
}
