<?php declare(strict_types=1);

namespace JoliPotage\Html\Parser\Tokenizer;

class EntitySearchNode
{
    /**
     * @var EntitySearchNode[]
     */
    public $children = [];
    /**
     * @var string
     */
    public $value;
}
