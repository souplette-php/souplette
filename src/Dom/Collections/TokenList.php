<?php declare(strict_types=1);

namespace Souplette\Dom\Collections;

use Souplette\Dom\Element;
use Souplette\Dom\Exception\InvalidCharacterError;
use Souplette\Dom\Exception\SyntaxError;
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
    private string $previousValue;

    public function __construct(Element $element, string $attributeName)
    {
        $this->tokenSet = new OrderedTokenSet();
        $this->elementRef = WeakReference::create($element);
        $this->attributeName = $attributeName;
        $this->previousValue = $element->getAttribute($attributeName) ?? '';
        $this->tokenSet->parse($this->previousValue);
    }

    public function __get(string $name)
    {
        return match ($name) {
            'value' => $this->getValue(),
            'length' => $this->count(),
        };
    }

    public function __set(string $name, string $value)
    {
        match ($name) {
            'value' => $this->setValue($value),
        };
    }

    public function setValue(string $value): void
    {
        $this->tokenSet->parse($value);
        $this->updateAttribute();
    }

    public function getValue(): string
    {
        $this->synchronize();
        return $this->tokenSet->serialize();
    }

    public function contains(string $token): bool
    {
        $this->synchronize();
        return $this->tokenSet->contains($token);
    }

    public function add(string ...$tokens): void
    {
        $this->validateTokens(...$tokens);
        $this->synchronize();
        foreach ($tokens as $token) {
            $this->tokenSet->add($token);
        }
        $this->updateAttribute();
    }

    public function remove(string ...$tokens): void
    {
        $this->validateTokens(...$tokens);
        $this->synchronize();
        foreach ($tokens as $token) {
            $this->tokenSet->remove($token);
        }
        $this->updateAttribute();
    }

    public function replace(string $old, string $new): void
    {
        $this->validateTokens($old, $new);
        $this->synchronize();
        $this->tokenSet->replace($old, $new);
        $this->updateAttribute();
    }

    public function toggle(string $token, ?bool $force = null): bool
    {
        $this->synchronize();
        $this->validateTokens($token);
        $result = $this->tokenSet->toggle($token, $force);
        $this->updateAttribute();
        return $result;
    }

    public function item(int $offset)
    {
        return $this->tokenSet->offsetGet($offset);
    }

    public function count(): int
    {
        $this->synchronize();
        return $this->tokenSet->count();
    }

    public function getIterator(): \Traversable
    {
        $this->synchronize();
        return $this->tokenSet->getIterator();
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

    private function getAttributeValue(): string
    {
        /** @var Element $element */
        $element = $this->elementRef->get();
        return $element->getAttribute($this->attributeName) ?? '';
    }

    private function synchronize()
    {
        $value = $this->getAttributeValue();
        if ($value === $this->previousValue) {
            return;
        }
        $this->previousValue = $value;
        $this->tokenSet->parse($value);
    }

    private function updateAttribute()
    {
        /** @var Element $element */
        $element = $this->elementRef->get();
        if (!$element) return;
        // 1. If the associated element does not have an associated attribute and token set is empty, then return.
        if (!$element->hasAttribute($this->attributeName) && $this->tokenSet->isEmpty()) {
            return;
        }
        // 2. Set an attribute value for the associated element using associated attribute’s local name
        // and the result of running the ordered set serializer for token set.
        $newValue = $this->tokenSet->serialize();
        $element->setAttribute($this->attributeName, $newValue);
        $this->previousValue = $newValue;
    }
}
