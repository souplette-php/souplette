<?php declare(strict_types=1);

namespace Souplette\DOM;

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
        $this->_value = $data;
        $this->length = mb_strlen($data, 'utf-8');
    }

    public function isEqualNode(?Node $otherNode): bool
    {
        if (!$otherNode) return false;
        if ($otherNode === $this) return true;
        return $otherNode->nodeType === $this->nodeType
            && $this->target === $otherNode->target
            && $this->_value === $otherNode->_value;
    }

    protected function clone(?Document $document, bool $deep = false): static
    {
        $copy = new self($this->target, $this->_value);
        $copy->_doc = $document ?? $this->_doc;
        return $copy;
    }

}
