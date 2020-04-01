<?php declare(strict_types=1);

namespace JoliPotage\Html\Parser\Tokenizer\Token;

use JoliPotage\Html\Parser\Tokenizer\Token;
use JoliPotage\Html\Parser\Tokenizer\TokenTypes;

final class EOF extends Token
{
    public int $type = TokenTypes::EOF;
}
