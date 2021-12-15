<?php declare(strict_types=1);

namespace Souplette\HTML\Sanitizer;

use Souplette\DOM\Namespaces;

final class SanitizerConfig
{
    private ?string $defaultNamespace = null;
    private array $namespaces = [];

    /**
     * The element allow list is a sequence of strings with elements
     * that the sanitizer should retain in the input.
     * @var array<string, array<string, bool>>
     */
    private array $allowedElements = [];
    /**
     * The element block list is a sequence of strings with elements
     * where the sanitizer should remove the elements from the input, but retain their children.
     * @var array<string, array<string, bool>>
     */
    private array $blockedElements = [];
    /**
     * The element drop list is a sequence of strings with elements
     * that the sanitizer should remove from the input, including its children.
     * @var array<string, array<string, bool>>
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

    public function __construct(?array $namespaces = null, ?string $defaultNamespace = null)
    {
        $this->defaultNamespace = $defaultNamespace;
        if ($namespaces === null) {
            $this->namespaces = Defaults::NAMESPACES;
        } else {
            $this->namespaces = $namespaces;
        }
    }


    public static function create(?array $namespaces = null, ?string $defaultNamespace = null): self
    {
        return new self($namespaces, $defaultNamespace);
    }

    public static function from(self $other): self
    {
        return clone $other;
    }

    public function withDefaultNamespace(?string $namespace): self
    {
        $this->defaultNamespace = $namespace ?: null;
        return $this;
    }

    /**
     * @param array<string, string> $namespaces
     */
    public function withNamespaces(array $namespaces): self
    {
        $this->namespaces = $namespaces;
        return $this;
    }

    public function getDefaultNamespace(): ?string
    {
        return $this->defaultNamespace;
    }

    public function allowElements(string ...$elements): self
    {
        foreach ($elements as $element) {
            [$ns, $localName] = $this->normalizeCSSQualifiedName($element);
            $this->allowedElements[$ns][$localName] = true;
        }
        return $this;
    }

    public function blockElements(string ...$elements): self
    {
        foreach ($elements as $element) {
            [$ns, $localName] = $this->normalizeCSSQualifiedName($element);
            $this->blockedElements[$ns][$localName] = true;
        }
        return $this;
    }

    public function dropElements(string ...$elements): self
    {
        foreach ($elements as $element) {
            [$ns, $localName] = $this->normalizeCSSQualifiedName($element);
            $this->droppedElements[$ns][$localName] = true;
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

    public function shouldAllowElement(string $name, ?string $namespace): bool
    {
        if (!$this->allowedElements) {
            return Defaults::ALLOW_ELEMENTS[$name] ?? false;
        }
        if (isset($this->allowedElements['*'][$name])) return true;
        return $this->allowedElements[$namespace][$name] ?? false;
    }

    public function shouldDropElement(string $name, ?string $namespace): bool
    {
        if (isset($this->droppedElements['*'][$name])) return true;
        return $this->droppedElements[$namespace][$name] ?? false;
    }

    public function shouldBlockElement(string $name, ?string $namespace): bool
    {
        if (isset($this->blockedElements['*'][$name])) return true;
        return $this->blockedElements[$namespace][$name] ?? false;
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

    /**
     * @return array{?string, string}
     */
    private function normalizeCSSQualifiedName(string $qualifiedName): array
    {
        $localName = $qualifiedName;
        $ns = null;

        $parts = explode('|', $qualifiedName, 2);
        if (\count($parts) === 1) {
            $ns = $this->defaultNamespace ?? '*';
        } else {
            [$prefix, $localName] = $parts;
            if ($prefix) {
                if (!isset($this->namespaces[$prefix])) {
                    throw new \RuntimeException(sprintf(
                        'No namespace provided for prefix "%s".',
                        $prefix,
                    ));
                }
                $ns = $this->namespaces[$prefix];
            }
        }

        // TODO: case-sensitivity
        return [$ns, strtolower($localName)];
    }
}
