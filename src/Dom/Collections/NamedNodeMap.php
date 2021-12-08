<?php declare(strict_types=1);

namespace Souplette\Dom\Collections;

use Souplette\Dom\Attr;
use Souplette\Dom\Element;
use Souplette\Dom\Exception\InUseAttributeError;
use Souplette\Dom\Exception\NotFoundError;
use Souplette\Dom\Exception\UndefinedProperty;
use Traversable;

/**
 * Work-in-progress, so just
 * @codeCoverageIgnore
 *
 * @property-read int $length
 */
final class NamedNodeMap implements \Countable, \IteratorAggregate, \ArrayAccess
{
    public function __construct(
        private readonly Element $element,
    ) {
    }

    public function __get(string $prop)
    {
        return match ($prop) {
            'length' => \count($this->element->_attrs),
            default => throw UndefinedProperty::forRead($this, $prop),
        };
    }

    public function item(int $index): ?Attr
    {
        return $this->element->_attrs[$index] ?? null;
    }

    public function getNamedItem(string $qualifiedName): ?Attr
    {
        return $this->element->getAttributeNode($qualifiedName);
    }

    public function getNamedItemNS(?string $namespace, string $localName): ?Attr
    {
        return $this->element->getAttributeNodeNS($namespace, $localName);
    }

    /**
     * @throws InUseAttributeError
     */
    public function setNamedItem(Attr $attr): ?Attr
    {
        return $this->element->setAttributeNode($attr);
    }

    /**
     * @throws InUseAttributeError
     */
    public function setNamedItemNS(Attr $attr): ?Attr
    {
        return $this->element->setAttributeNode($attr);
    }

    /**
     * @throws NotFoundError
     */
    public function removeNamedItem(string $qualifiedName): Attr
    {
        $attr = $this->element->removeAttribute($qualifiedName);
        if (!$attr) {
            throw new NotFoundError();
        }
        return $attr;
    }

    /**
     * @throws NotFoundError
     */
    public function removeNamedItemNS(?string $namespace, string $localName): Attr
    {
        $attr = $this->element->removeAttributeNS($namespace, $localName);
        if (!$attr) {
            throw new NotFoundError();
        }
        return $attr;
    }

    public function getLength(): int
    {
        return \count($this->element->_attrs);
    }

    public function count(): int
    {
        return \count($this->element->_attrs);
    }

    public function getIterator(): Traversable
    {
        yield from $this->element->_attrs;
    }

    public function offsetExists(mixed $offset): bool
    {
        if (\is_int($offset)) return isset($this->element->_attrs[$offset]);
        if (\is_string($offset)) return $this->element->hasAttribute($offset);
        return false;
    }

    public function offsetGet(mixed $offset): ?Attr
    {
        if (\is_int($offset)) return $this->element->_attrs[$offset] ?? null;
        if (\is_string($offset)) return $this->element->getAttributeNode($offset);
        return null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
    }

    public function offsetUnset(mixed $offset): void
    {
    }
}
