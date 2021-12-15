<?php declare(strict_types=1);

namespace Souplette\DOM;

use Souplette\DOM\Api\ChildNodeInterface;
use Souplette\DOM\Traits\ChildNodeTrait;

final class DocumentType extends Node implements ChildNodeInterface
{
    use ChildNodeTrait;

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

    protected function clone(?Document $document, bool $deep = false): static
    {
        $copy = new self($this->name, $this->publicId, $this->systemId);
        $copy->_doc = $document ?? $this->_doc;
        return $copy;
    }

    /**
     * @see https://dom.spec.whatwg.org/#dom-node-lookupprefix
     */
    public function lookupPrefix(?string $namespace): ?string
    {
        return null;
    }

    protected function locateNamespace(?string $prefix): ?string
    {
        return null;
    }

    protected function insertedInto(ParentNode $insertionPoint): void
    {
        parent::insertedInto($insertionPoint);
        $this->_doc->_doctype = $this;
    }

    protected function removedFrom(ParentNode $insertionPoint): void
    {
        parent::removedFrom($insertionPoint);
        $this->_doc->_doctype = null;
    }
}
