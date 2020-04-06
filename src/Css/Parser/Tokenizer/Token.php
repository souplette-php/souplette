<?php declare(strict_types=1);

namespace JoliPotage\Css\Parser\Tokenizer;

abstract class Token
{
    public int $type;
    public int $position;
    /**
     * @see https://www.w3.org/TR/css-syntax-3/#representation
     */
    public string $representation;
}
