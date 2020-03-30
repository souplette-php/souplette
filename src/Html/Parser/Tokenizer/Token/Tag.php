<?php declare(strict_types=1);

namespace JoliPotage\Html\Parser\Tokenizer\Token;

use JoliPotage\Html\Parser\Tokenizer\Token;

abstract class Tag extends Token
{
    /**
     * @var string
     */
    public $name = '';
    /**
     * @var bool
     */
    public $selfClosing = false;
    /**
     * @var array|null
     */
    public $attributes;

    public function __construct(string $name = '')
    {
        $this->name = $name;
    }
}
