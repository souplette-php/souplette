<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Tokenizer\Token;

use JoliPotage\Css\Syntax\Tokenizer\Token;

abstract class NumericToken extends Token
{
    /**
     * @var int|float
     */
    public $value;
    public string $representation;
    public bool $isInteger;

    public function __construct(string $value, int $position)
    {
        $this->representation = $value;
        $this->position = $position;
        $this->value = (0 + $value);
        $this->isInteger = is_integer($this->value);
    }
}
