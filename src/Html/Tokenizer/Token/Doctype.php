<?php declare(strict_types=1);

namespace Souplette\Html\Tokenizer\Token;

use Souplette\Html\Tokenizer\Token;
use Souplette\Html\Tokenizer\TokenType;

final class Doctype extends Token
{
    const TYPE = TokenType::DOCTYPE;
    public string $name = '';
    public ?string $publicIdentifier = null;
    public ?string $systemIdentifier = null;
    public bool $forceQuirks = false;
}
