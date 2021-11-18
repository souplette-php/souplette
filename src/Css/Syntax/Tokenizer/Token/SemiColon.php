<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Tokenizer\Token;

use Souplette\Css\Syntax\Tokenizer\TokenType;

final class SemiColon extends SingleCharToken
{
    const TYPE = TokenType::SEMICOLON;
    public string $value = ';';
    public string $representation = ';';
}
