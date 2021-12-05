<?php declare(strict_types=1);

namespace Souplette\Dom;

final class Comment extends CharacterData
{
    public readonly int $nodeType;
    public readonly string $nodeName;

    public function __construct(string $data = '')
    {
        $this->nodeType = Node::COMMENT_NODE;
        $this->nodeName = '#comment';
        $this->value = $data;
        $this->length = mb_strlen($data, 'utf-8');
    }

    public function cloneNode(bool $deep = false): static
    {
        $copy = new self($this->value);
        $copy->document = $this->document;
        return $copy;
    }
}
