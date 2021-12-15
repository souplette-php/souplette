<?php declare(strict_types=1);

namespace Souplette\HTML\Tokenizer;

class EntitySearchNode
{
    /**
     * @var EntitySearchNode[]
     */
    public array $children = [];
    public ?string $value = null;
}
