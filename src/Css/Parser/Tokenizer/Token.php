<?php declare(strict_types=1);

namespace JoliPotage\Css\Parser\Tokenizer;

abstract class Token
{
    public int $type;
    public int $position;
}
