<?php declare(strict_types=1);

namespace Souplette\Dom\Node;

final class DocumentType extends Node
{
    public readonly int $nodeType;
    public readonly string $nodeName;

    public function __construct(
        public readonly string $name,
        public readonly string $publicId = '',
        public readonly string $systemId = '',
    ) {
        $this->nodeType = Node::DOCUMENT_TYPE_NODE;
        $this->nodeName = $name;
    }

    public function isEqualNode(?Node $otherNode): bool
    {
        if (!$otherNode) return false;
        if ($otherNode === $this) return true;
        return $otherNode->nodeType === $this->nodeType && (
            $this->name === $otherNode->name
            && $this->publicId === $otherNode->publicId
            && $this->systemId === $otherNode->systemId
        );
    }

    public function cloneNode(bool $deep = false): static
    {
        $copy = new self($this->name, $this->publicId, $this->systemId);
        $copy->document = $this->document;
        return $copy;
    }
}
