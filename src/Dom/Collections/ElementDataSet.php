<?php declare(strict_types=1);

namespace Souplette\Dom\Collections;

use Souplette\Dom\Element;
use Souplette\Dom\Exception\SyntaxError;
use Traversable;
use WeakReference;

final class ElementDataSet implements \ArrayAccess, \IteratorAggregate
{
    /**
     * @var WeakReference<Element>
     */
    private WeakReference $elementRef;

    public function __construct(Element $element)
    {
        $this->elementRef = WeakReference::create($element);
    }

    public function __get(string $prop): mixed
    {
        foreach ($this->elementRef->get()?->_attrs as $attr) {
            if ($this->propertyMatchesAttributeName($prop, $attr->localName)) {
                return $attr->_value;
            }
        }
        return null;
    }

    public function __set(string $prop, mixed $value): void
    {
        if (!$this->isValidPropertyName($prop)) {
            throw new SyntaxError(sprintf(
                '"%s" is not a valid property name.',
                $prop,
            ));
        }
        $this->elementRef->get()?->setAttribute(
            $this->convertPropertyName($prop),
            $value,
        );
    }

    public function __isset(string $prop): bool
    {
        foreach ($this->elementRef->get()?->_attrs as $attr) {
            if ($this->propertyMatchesAttributeName($prop, $attr->localName)) {
                return true;
            }
        }
        return false;
    }

    public function __unset(string $prop): void
    {
        if ($this->isValidPropertyName($prop)) {
            $attr = $this->convertPropertyName($prop);
            $this->elementRef->get()?->removeAttribute($attr);
        }
    }

    public function getIterator(): Traversable
    {
        foreach ($this->elementRef->get()?->_attrs as $attr) {
            if ($this->isValidAttributeName($attr->localName)) {
                yield $this->convertAttributeName($attr->localName) => $attr->_value;
            }
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->__isset((string)$offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get((string)$offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->__set((string)$offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->__unset((string)$offset);
    }

    private function isValidAttributeName(string $name): bool
    {
        return preg_match('/^data-[^A-Z-]*$/', $name);
    }

    private function isValidPropertyName(string $prop): bool
    {
        return !preg_match('/-[a-z]/', $prop);
    }

    private function convertAttributeName(string $attr): string
    {
        return str_replace('-', '', lcfirst(ucwords(substr($attr, 5), '-')));
    }

    private function convertPropertyName(string $prop): string
    {
        return 'data-' . strtolower(preg_replace('/[A-Z]/', '-$0', $prop));
    }

    private function propertyMatchesAttributeName(string $prop, string $attr): bool {
        $propLen = \strlen($prop);
        $attrLen = \strlen($attr);
        $p = 0;
        $a = 5;
        $atBoundary = false;
        while ($a < $attrLen && $p < $propLen) {
            if ($attr[$a] === '-' && ctype_lower($attr[$a + 1] ?? '')) {
                $atBoundary = true;
            } else {
                if ($prop[$p] !== ($atBoundary ? strtoupper($attr[$a]) : $attr[$a])) {
                    return false;
                }
                $p++;
                $atBoundary = false;
            }
            $a++;
        }
        return $a === $attrLen && $p === $propLen;
    }
}
