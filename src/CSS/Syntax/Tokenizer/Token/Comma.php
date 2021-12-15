<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\Tokenizer\Token;

use Souplette\CSS\Syntax\Tokenizer\TokenType;

final class Comma extends SingleCharToken
{
    const TYPE = TokenType::COMMA;
    public string $value = ',';
    public string $representation = ',';
}
