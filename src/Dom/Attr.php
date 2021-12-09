<?php declare(strict_types=1);

namespace Souplette\Dom;

/**
 * @property-read ?Element $ownerElement
 * @property string $value
 * @property-read true $specified
 */
final class Attr extends Node
{
    public readonly int $nodeType;
    public readonly string $nodeName;
    public readonly string $name;

    public function __construct(
        public readonly string $localName,
        public readonly ?string $namespaceURI = null,
        public readonly ?string $prefix = null,
    ) {
        $this->nodeType = Node::ATTRIBUTE_NODE;
        $this->name = $prefix ? "{$prefix}:{$localName}" : $localName;
        $this->nodeName = $this->name;
        $this->_value = '';
    }

    public function __get(string $prop)
    {
        return match ($prop) {
            'value', 'nodeValue', 'textContent' => $this->_value,
            'ownerElement' => $this->_parent,
            'parentNode', 'parentElement', 'firstChild', 'lastChild',
            'nextSibling', 'previousSibling' => null,
            'specified' => true,
            default => parent::__get($prop),
        };
    }

    public function __set(string $prop, mixed $value)
    {
        match ($prop) {
            'value', 'nodeValue' => $this->setValue($value),
            default => parent::__set($prop, $value),
        };
    }

    public function getValue(): string
    {
        return $this->_value;
    }

    public function setValue(string $value): void
    {
        if ($this->_parent) {
            $this->_parent->setAttribute($this->name, $value);
        } else {
            $this->_value = $value;
        }
    }

    public function getNodeValue(): string
    {
        return $this->_value;
    }

    public function setNodeValue(?string $value): void
    {
        $this->setValue($value ?? '');
    }

    public function getTextContent(): string
    {
        return $this->_value;
    }

    public function setTextContent(?string $value): void
    {
        $this->setValue($value ?? '');
    }

    public function getOwnerElement(): ?Element
    {
        return $this->_parent;
    }

    public function isEqualNode(?Node $otherNode): bool
    {
        if (!$otherNode) return false;
        if ($otherNode === $this) return true;
        return $otherNode->nodeType === $this->nodeType && (
            $this->name === $otherNode->name
            && $this->_value === $otherNode->_value
            && $this->namespaceURI === $otherNode->namespaceURI
        );
    }

    protected function clone(?Document $document, bool $deep = false): static
    {
        $copy = new self($this->localName, $this->namespaceURI, $this->prefix);
        $copy->_doc = $document ?? $this->_doc;
        $copy->_value = $this->_value;
        return $copy;
    }

    /**
     * @see https://dom.spec.whatwg.org/#dom-node-lookupprefix
     */
    public function lookupPrefix(?string $namespace): ?string
    {
        if (!$namespace || !$this->_parent) return null;
        return $this->_parent->locateNamespacePrefix($namespace);
    }

    protected function locateNamespace(?string $prefix): ?string
    {
        if (!$this->_parent) return null;
        return $this->_parent->locateNamespace($prefix);
    }
}
