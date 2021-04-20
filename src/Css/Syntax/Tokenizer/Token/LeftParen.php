<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Tokenizer\Token;

use Souplette\Css\Syntax\Tokenizer\TokenTypes;

final class LeftParen extends SingleCharToken
{
    const TYPE = TokenTypes::LPAREN;
    public string $value = '(';
    public string $representation = '(';
}
