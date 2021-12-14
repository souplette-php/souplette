<?php declare(strict_types=1);

namespace Souplette\Html\Sanitizer;

final class SanitizerConfig
{
    /**
     * The element allow list is a sequence of strings with elements
     * that the sanitizer should retain in the input.
     * @var array<string, bool>
     */
    private array $allowedElements = [];
    /**
     * The element block list is a sequence of strings with elements
     * where the sanitizer should remove the elements from the input, but retain their children.
     * @var array<string, bool>
     */
    private array $blockedElements = [];
    /**
     * The element drop list is a sequence of strings with elements
     * that the sanitizer should remove from the input, including its children.
     * @var array<string, bool>
     */
    private array $droppedElements = [];
    /**
     * The attribute allow list is an attribute match list,
     * which determines whether an attribute (on a given element) should be allowed.
     * @var array<string, array<string, bool>>
     */
    private array $allowedAttributes = [];
    /**
     * The attribute drop list is an attribute match list,
     * which determines whether an attribute (on a given element) should be dropped.
     * @var array<string, array<string, bool>>
     */
    private array $droppedAttributes = [];

    /**
     * The allowComments option determines whether HTML comments are allowed.
     */
    private bool $allowsComments = false;
    /**
     * The allowCustomElements option determines whether custom elements are to be considered.
     * The default is to drop them.
     * If this option is true, custom elements will still be checked against all other built-in or configured checks.
     */
    private bool $allowsCustomElements = false;

    public static function create(): self
    {
        return new self();
    }

    public static function from(self $other): self
    {
        return clone $other;
    }

    public function allowElements(string ...$elements): self
    {
        foreach ($elements as $element) {
            $this->allowedElements[strtolower($element)] = true;
        }
        return $this;
    }

    public function blockElements(string ...$elements): self
    {
        foreach ($elements as $element) {
            $this->blockedElements[strtolower($element)] = true;
        }
        return $this;
    }

    public function dropElements(string ...$elements): self
    {
        foreach ($elements as $element) {
            $this->droppedElements[strtolower($element)] = true;
        }
        return $this;
    }

    public function allowAttribute(string $attribute, array $elements): self
    {
        $attr = strtolower($attribute);
        if (\in_array('*', $elements)) {
            $this->allowedAttributes[$attr]['*'] = true;
            return $this;
        }
        foreach ($elements as $element) {
            $this->allowedAttributes[$attr][strtolower($element)] = true;
        }
        return $this;
    }

    public function dropAttribute(string $attribute, array $elements): self
    {
        $attr = strtolower($attribute);
        if (\in_array('*', $elements)) {
            $this->droppedAttributes[$attr]['*'] = true;
            return $this;
        }
        foreach ($elements as $element) {
            $this->droppedAttributes[$attr][strtolower($element)] = true;
        }
        return $this;
    }

    public function allowComments(): self
    {
        $this->allowsComments = true;
        return $this;
    }

    public function disallowComments(): self
    {
        $this->allowsComments = false;
        return $this;
    }

    public function allowCustomElements(): self
    {
        $this->allowsCustomElements = true;
        return $this;
    }

    public function disallowCustomElements(): self
    {
        $this->allowsCustomElements = false;
        return $this;
    }

    public function shouldAllowComments(): bool
    {
        return $this->allowsComments;
    }

    public function shouldAllowCustomElements(): bool
    {
        return $this->allowsCustomElements;
    }

    public function shouldAllowElement(string $name): bool
    {
        if (!$this->allowedElements) {
            return Defaults::ALLOW_ELEMENTS[$name] ?? false;
        }
        return $this->allowedElements[$name] ?? false;
    }

    public function shouldDropElement(string $name): bool
    {
        return $this->droppedElements[$name] ?? false;
    }

    public function shouldBlockElement(string $name): bool
    {
        return $this->blockedElements[$name] ?? false;
    }

    public function shouldAllowAttribute(string $attr, string $element): bool
    {
        if (!$this->allowedAttributes) {
            return Defaults::ALLOW_ATTRIBUTES[$attr] ?? false;
        }
        return (
            isset($this->allowedAttributes[$attr][$element])
            || isset($this->allowedAttributes[$attr]['*'])
        );
    }

    public function shouldDropAttribute(string $attr, string $element): bool
    {
        return (
            isset($this->droppedAttributes[$attr][$element])
            || isset($this->droppedAttributes[$attr]['*'])
        );
    }
}
