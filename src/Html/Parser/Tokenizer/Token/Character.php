<?php declare(strict_types=1);

namespace JoliPotage\Html\Parser\Tokenizer\Token;

use JoliPotage\Html\Parser\Tokenizer\Token;
use JoliPotage\Html\Parser\Tokenizer\TokenTypes;

final class Character extends Token
{
    /**
     * @var int
     */
    public $type = TokenTypes::CHARACTER;
    /**
     * @var string
     */
    public $data;

    public function __construct(string $data = '')
    {
        $this->data = $data;
    }
}
