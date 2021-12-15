<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\Tokenizer\Token;

use Souplette\CSS\Syntax\Tokenizer\TokenType;

final class LeftBracket extends SingleCharToken
{
    const TYPE = TokenType::LBRACK;
    public string $value = '[';
    public string $representation = '[';
}
