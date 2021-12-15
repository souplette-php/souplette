<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\Tokenizer\Token;

use Souplette\CSS\Syntax\Tokenizer\TokenType;

final class Percentage extends NumericToken
{
    const TYPE = TokenType::PERCENTAGE;
}
