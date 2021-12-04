<?php declare(strict_types=1);

namespace Souplette\Dom\Node;

final class ProcessingInstruction extends CharacterData
{
    public readonly int $nodeType;
    public readonly string $nodeName;

    public function __construct(
        public readonly string $target,
        string $data = '',
    ) {
        $this->nodeType = Node::PROCESSING_INSTRUCTION_NODE;
        $this->nodeName = $target;
        $this->value = $data;
        $this->length = mb_strlen($data, 'utf-8');
    }

    public function isEqualNode(?Node $otherNode): bool
    {
        if (!$otherNode) return false;
        if ($otherNode === $this) return true;
        return $otherNode->nodeType === $this->nodeType
            && $this->target === $otherNode->target
            && $this->value === $otherNode->value;
    }

    public function cloneNode(bool $deep = false): static
    {
        $copy = new self($this->target, $this->value);
        $copy->document = $this->document;
        return $copy;
    }
}
