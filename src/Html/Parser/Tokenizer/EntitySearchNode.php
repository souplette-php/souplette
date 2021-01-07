<?php declare(strict_types=1);

namespace Souplette\Html\Parser\Tokenizer;

class EntitySearchNode
{
    /**
     * @var EntitySearchNode[]
     */
    public array $children = [];
    public ?string $value = null;
}
