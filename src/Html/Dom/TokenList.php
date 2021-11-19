<?php declare(strict_types=1);

namespace Souplette\Html\Dom;

use Souplette\Html\Dom\Exception\InvalidCharacter;
use Souplette\Html\Dom\Exception\SyntaxError;
use Souplette\Html\Dom\Internal\OrderedTokenSet;
use Souplette\Html\Dom\Node\Element;
use WeakReference;

/**
 * @see https://dom.spec.whatwg.org/#interface-domtokenlist
 *
 * @property string $value
 * @property-read int $length
 */
final class TokenList implements \Countable, \IteratorAggregate
{
    private OrderedTokenSet $tokenSet;
    /**
     * @var WeakReference<Element>
     */
    private WeakReference $elementRef;
    private string $attributeName;
    private string $previousValue;

    public function __construct(Element $element, string $attributeName)
    {
        $this->tokenSet = new OrderedTokenSet();
        $this->elementRef = WeakReference::create($element);
        $this->attributeName = $attributeName;
        $this->previousValue = $element->getAttribute($attributeName);
        $this->tokenSet->parse($this->previousValue);
    }

    public function __get($name)
    {
        if ($name === 'value') {
            return $this->getValue();
        } elseif ($name === 'length') {
            return $this->count();
        }
    }

    public function __set($name, $value)
    {
        if ($name === 'value') {
            $this->setValue($value);
        }
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

    public function getIterator()
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
            } elseif (\strcspn($token, " \n\t\f") !== \strlen($token)) {
                throw new InvalidCharacter('Token contains whitespace.');
            }
        }
    }

    private function getAttributeValue(): string
    {
        /** @var Element $element */
        $element = $this->elementRef->get();
        return $element->getAttribute($this->attributeName);
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
        // 1. If the associated element does not have an associated attribute and token set is empty, then return.
        if (!$element->hasAttribute($this->attributeName) && $this->tokenSet->isEmpty()) {
            return;
        }
        // 2. Set an attribute value for the associated element using associated attribute’s local name
        // and the result of running the ordered set serializer for token set.
        $newValue = $this->tokenSet->serialize();
        $attr = $element->getAttributeNode($this->attributeName);
        $attr->value = $newValue;
        $this->previousValue = $newValue;
    }
}
