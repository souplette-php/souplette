<?php declare(strict_types=1);

namespace Souplette\DOM\Collections;

use Souplette\DOM\Element;
use Souplette\DOM\Exception\InvalidCharacterError;
use Souplette\DOM\Exception\SyntaxError;
use Souplette\Exception\UndefinedProperty;
use WeakReference;

/**
 * @see https://dom.spec.whatwg.org/#interface-domtokenlist
 *
 * @property string $value
 * @property-read int $length
 */
final class TokenList implements \Countable, \IteratorAggregate
{
    private readonly OrderedTokenSet $tokenSet;
    /**
     * @var WeakReference<Element>
     */
    private readonly WeakReference $elementRef;
    private readonly string $attributeName;
    private bool $isUpdating = false;

    public function __construct(Element $element, string $attributeName)
    {
        $this->tokenSet = new OrderedTokenSet();
        $this->elementRef = WeakReference::create($element);
        $this->attributeName = $attributeName;
        $this->tokenSet->parse($element->getAttribute($attributeName) ?? '');
    }

    public function __get(string $prop)
    {
        return match ($prop) {
            'value' => $this->getValue(),
            'length' => $this->count(),
            default => throw UndefinedProperty::forRead($this, $prop),
        };
    }

    public function __set(string $prop, string $value)
    {
        match ($prop) {
            'value' => $this->setValue($value),
            default => throw UndefinedProperty::forWrite($this, $prop),
        };
    }

    public function setValue(string $value): void
    {
        $this->elementRef->get()?->setAttribute($this->attributeName, $value);
    }

    public function getValue(): string
    {
        return $this->tokenSet->serialize();
    }

    public function contains(string $token): bool
    {
        return $this->tokenSet->contains($token);
    }

    public function add(string ...$tokens): void
    {
        if (!$tokens) return;
        $this->validateTokens(...$tokens);
        foreach ($tokens as $token) {
            $this->tokenSet->add($token);
        }
        $this->updateAttribute();
    }

    public function remove(string ...$tokens): void
    {
        if (!$tokens) return;
        $this->validateTokens(...$tokens);
        foreach ($tokens as $token) {
            $this->tokenSet->remove($token);
        }
        $this->updateAttribute();
    }

    public function replace(string $old, string $new): void
    {
        $this->validateTokens($old, $new);
        $this->tokenSet->replace($old, $new);
        $this->updateAttribute();
    }

    public function toggle(string $token, ?bool $force = null): bool
    {
        $this->validateTokens($token);
        if ($this->tokenSet->contains($token)) {
            if (!$force) {
                $this->tokenSet->remove($token);
                $this->updateAttribute();
                return false;
            }
            return true;
        }
        if ($force || $force === null) {
            $this->tokenSet->add($token);
            $this->updateAttribute();
            return true;
        }
        return false;
    }

    public function item(int $offset): ?string
    {
        return $this->tokenSet->offsetGet($offset);
    }

    public function count(): int
    {
        return $this->tokenSet->count();
    }

    public function getIterator(): \Traversable
    {
        yield from $this->tokenSet;
    }

    public function __toString()
    {
        return $this->getValue();
    }

    private function validateTokens(string ...$tokens)
    {
        foreach ($tokens as $token) {
            if ($token === '') {
                throw new SyntaxError('Empty token.');
            } else if (strcspn($token, " \n\t\f") !== \strlen($token)) {
                throw new InvalidCharacterError('Token contains whitespace.');
            }
        }
    }

    private function updateAttribute()
    {
        $this->isUpdating = true;
        $this->elementRef->get()?->setAttribute($this->attributeName, $this->tokenSet->serialize());
        $this->isUpdating = false;
    }

    /**
     * @internal
     */
    public function notifyAttributeChanged(?string $oldValue, ?string $newValue): void
    {
        if ($this->isUpdating) return;
        $this->tokenSet->parse($newValue ?? '');
    }
}
