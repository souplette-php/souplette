<?php declare(strict_types=1);

namespace JoliPotage\Html\Parser\Tokenizer\Token;

use JoliPotage\Html\Parser\Tokenizer\Token;
use JoliPotage\Html\Parser\Tokenizer\TokenTypes;

final class Doctype extends Token
{
    /**
     * @var int
     */
    public $type = TokenTypes::DOCTYPE;
    /**
     * @var string
     */
    public $name = '';
    /**
     * @var string|null
     */
    public $publicIdentifier;
    /**
     * @var string|null
     */
    public $systemIdentifier;
    /**
     * @var bool
     */
    public $forceQuirks = false;
}
