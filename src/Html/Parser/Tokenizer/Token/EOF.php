<?php declare(strict_types=1);

namespace Souplette\Html\Parser\Tokenizer\Token;

use Souplette\Html\Parser\Tokenizer\Token;
use Souplette\Html\Parser\Tokenizer\TokenTypes;

final class EOF extends Token
{
    const TYPE = TokenTypes::EOF;
}
