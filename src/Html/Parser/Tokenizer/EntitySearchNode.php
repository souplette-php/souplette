<?php declare(strict_types=1);

namespace JoliPotage\Html\Parser\Tokenizer;

class EntitySearchNode
{
    /**
     * @var EntitySearchNode[]
     */
    public array $children = [];
    public ?string $value = null;
}
