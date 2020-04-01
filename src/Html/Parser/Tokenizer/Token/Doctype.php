<?php declare(strict_types=1);

namespace JoliPotage\Html\Parser\Tokenizer\Token;

use JoliPotage\Html\Parser\Tokenizer\Token;
use JoliPotage\Html\Parser\Tokenizer\TokenTypes;

final class Doctype extends Token
{
    public int $type = TokenTypes::DOCTYPE;
    public string $name = '';
    public ?string $publicIdentifier = null;
    public ?string $systemIdentifier = null;
    public bool $forceQuirks = false;
}
