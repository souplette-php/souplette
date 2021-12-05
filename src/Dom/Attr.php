<?php declare(strict_types=1);

namespace Souplette\Dom;

/**
 * @property-read ?Element $ownerElement
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
        $this->value = '';
    }

    public function __get(string $prop)
    {
        return match ($prop) {
            'value', 'nodeValue', 'textContent' => $this->value,
            'ownerElement' => $this->parent,
            'parentNode', 'parentElement', 'firstChild', 'lastChild',
            'nextSibling', 'previousSibling' => null,
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
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getNodeValue(): string
    {
        return $this->value;
    }

    public function setNodeValue(string $value): void
    {
        $this->value = $value;
    }

    public function getTextContent(): string
    {
        return $this->value;
    }

    public function setTextContent(string $value): void
    {
        $this->value = $value;
    }

    public function getOwnerElement(): ?Element
    {
        return $this->parent;
    }

    public function isEqualNode(?Node $otherNode): bool
    {
        if (!$otherNode) return false;
        if ($otherNode === $this) return true;
        return $otherNode->nodeType === $this->nodeType && (
            $this->name === $otherNode->name
            && $this->value === $otherNode->value
            && $this->namespaceURI === $otherNode->namespaceURI
        );
    }

    public function cloneNode(bool $deep = false): static
    {
        $copy = new self($this->localName, $this->namespaceURI, $this->prefix);
        $copy->document = $this->document;
        $copy->value = $this->value;
        return $copy;
    }

    /**
     * @see https://dom.spec.whatwg.org/#dom-node-lookupprefix
     */
    public function lookupPrefix(?string $namespace): ?string
    {
        if (!$namespace || !$this->parent) return null;
        return $this->parent->locateNamespacePrefix($namespace);
    }

    protected function locateNamespace(?string $prefix): ?string
    {
        if (!$this->parent) return null;
        return $this->parent->locateNamespace($prefix);
    }
}