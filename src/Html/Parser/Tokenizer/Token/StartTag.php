<?php declare(strict_types=1);

namespace JoliPotage\Html\Parser\Tokenizer\Token;

use JoliPotage\Html\Parser\Tokenizer\TokenTypes;

final class StartTag extends Tag
{
    /**
     * @var int
     */
    public $type = TokenTypes::START_TAG;
}
