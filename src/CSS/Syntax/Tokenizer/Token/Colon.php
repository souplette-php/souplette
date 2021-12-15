<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\Tokenizer\Token;

use Souplette\CSS\Syntax\Tokenizer\TokenType;

final class Colon extends SingleCharToken
{
    const TYPE = TokenType::COLON;
    public string $value = ':';
    public string $representation = ':';
}
