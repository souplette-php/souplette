<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Tokenizer\Token;

use Souplette\Css\Syntax\Tokenizer\TokenTypes;

final class RightParen extends SingleCharToken
{
    const TYPE = TokenTypes::RPAREN;
    public string $value = ')';
    public string $representation = ')';
}
