<?php declare(strict_types=1);

namespace Souplette\Dom;

use Souplette\Css\Selectors\SelectorQuery;
use Souplette\Dom\Api\ChildNodeInterface;
use Souplette\Dom\Api\NonDocumentTypeChildNodeInterface;
use Souplette\Dom\Exception\InUseAttributeError;
use Souplette\Dom\Exception\InvalidCharacterError;
use Souplette\Dom\Exception\NamespaceError;
use Souplette\Dom\Exception\NoModificationAllowed;
use Souplette\Dom\Exception\NotFoundError;
use Souplette\Dom\Exception\SyntaxError;
use Souplette\Dom\Internal\TokenList;
use Souplette\Dom\Traits\ChildNodeTrait;
use Souplette\Dom\Traits\GetElementsByClassNameTrait;
use Souplette\Dom\Traits\GetElementsByTagNameTrait;
use Souplette\Dom\Traits\NonDocumentTypeChildNodeTrait;
use Souplette\Html\Parser;
use Souplette\Html\Serializer;
use Souplette\Xml\QName;

/**
 * @property string $id
 * @property string $className
 * @property string $innerHTML
 * @property string $outerHTML
 * @property-read Attr[] $attributes
 * @property-read TokenList $classList
 */
class Element extends ParentNode implements ChildNodeInterface, NonDocumentTypeChildNodeInterface
{
    use ChildNodeTrait;
    use NonDocumentTypeChildNodeTrait;
    use GetElementsByTagNameTrait;
    use GetElementsByClassNameTrait;

    public readonly int $nodeType;
    public readonly string $nodeName;
    public readonly string $localName;
    public readonly string $qualifiedName;
    public readonly string $tagName;
    public readonly ?string $namespaceURI;
    public readonly ?string $prefix;
    public readonly bool $isHTML;

    protected TokenList $classList;

    public function __construct(string $localName, ?string $namespace = null, ?string $prefix = null)
    {
        $this->nodeType = Node::ELEMENT_NODE;
        $this->localName = $localName;
        $this->namespaceURI = $namespace;
        $this->prefix = $prefix;
        $this->qualifiedName = $prefix ? "{$prefix}:{$localName}" : $localName;
        $this->isHTML = $namespace === Namespaces::HTML;
        if ($this->isHTML) {
            $this->tagName = strtoupper($this->qualifiedName);
        } else {
            $this->tagName = $this->qualifiedName;
        }
        $this->nodeName = $this->tagName;
    }

    public function __get(string $prop)
    {
        return match ($prop) {
            'attributes' => $this->attributes,
            'nextElementSibling' => $this->getNextElementSibling(),
            'previousElementSibling' => $this->getPreviousElementSibling(),
            'id' => $this->getId(),
            'className' => $this->getClassName(),
            'classList' => $this->getClassList(),
            'innerHTML' => $this->getInnerHTML(),
            'outerHTML' => $this->getOuterHTML(),
            default => parent::__get($prop),
        };
    }

    public function __set(string $prop, mixed $value)
    {
        match ($prop) {
            'id' => $this->setId($value),
            'className' => $this->setClassName($value),
            'innerHTML' => $this->setInnerHTML($value),
            'outerHTML' => $this->setOuterHTML($value),
            default => parent::__set($prop, $value),
        };
    }

    public function getId(): string
    {
        return $this->getAttribute('id') ?? '';
    }

    public function setId(string $id): void
    {
        $this->setAttribute('id', $id);
    }

    public function getClassName(): string
    {
        return $this->getAttribute('class') ?? '';
    }

    public function setClassName(string $className): void
    {
        $this->setAttribute('class', $className);
    }

    public function getClassList(): TokenList
    {
        return $this->classList ??= new TokenList($this, 'class');
    }

    public function matches(string $selector): bool
    {
        return SelectorQuery::matches($this, $selector);
    }

    public function closest(string $selector): ?Element
    {
        return SelectorQuery::closest($this, $selector);
    }

    public function isEqualNode(?Node $otherNode): bool
    {
        if (!$otherNode) return false;
        if ($otherNode === $this) return true;
        if ($otherNode->nodeType !== $this->nodeType) return false;
        foreach ($this->attributes as $attribute) {
            $otherAttr = $otherNode->getAttributeNS($attribute->namespaceURI, $attribute->localName);
            if (!$attribute->isEqualNode($otherAttr)) {
                return false;
            }
        }
        return $this->areChildrenEqual($otherNode);
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return string[]
     */
    public function getAttributeNames(): array
    {
        if (!$this->attributes) return [];
        return array_map(fn(Attr $attr) => $attr->name, $this->attributes);
    }

    public function getAttribute(string $qualifiedName): ?string
    {
        $attr = $this->getAttributeNode($qualifiedName);
        return $attr?->value;
    }

    public function getAttributeNS(?string $namespace, string $localName): ?string
    {
        $attr = $this->getAttributeNodeNS($namespace, $localName);
        return $attr?->value;
    }

    public function setAttribute(string $qualifiedName, string $value): void
    {
        if (!QName::isValidName($qualifiedName)) {
            throw new InvalidCharacterError();
        }
        if ($this->isHTML && $this->document?->isHTML) {
            $qualifiedName = strtolower($qualifiedName);
        }
        foreach ($this->attributes as $attr) {
            if ($attr->name === $qualifiedName) {
                $attr->value = $value;
                return;
            }
        }
        $attr = new Attr($qualifiedName);
        $attr->document = $this->document;
        $attr->parent = $this;
        $attr->value = $value;
        $this->attributes[] = $attr;
    }

    /**
     * @throws InvalidCharacterError
     * @throws NamespaceError
     */
    public function setAttributeNS(?string $namespace, string $qualifiedName, string $value): void
    {
        [$namespace, $prefix, $localName] = QName::validateAndExtract($qualifiedName, $namespace);
        $attribute = $this->getAttributeNodeNS($namespace, $localName);
        if (!$attribute) {
            $attribute = new Attr($localName, $namespace, $prefix);
            $attribute->document = $this->document;
            $attribute->parent = $this;
            $attribute->value = $value;
            $this->attributes[] = $attribute;
            return;
        }
        $attribute->value = $value;
    }

    public function removeAttribute(string $qualifiedName): ?Attr
    {
        if (!$this->attributes) return null;
        // 1. Let attr be the result of getting an attribute given qualifiedName and element.
        if ($this->isHTML && $this->document?->isHTML) {
            $qualifiedName = strtolower($qualifiedName);
        }
        foreach ($this->attributes as $i => $attr) {
            if ($attr->name === $qualifiedName) {
                array_splice($this->attributes, $i, 1);
                $attr->parent = null;
                return $attr;
            }
        }
        return null;
    }

    public function removeAttributeNS(?string $namespace, string $localName): ?Attr
    {
        if (!$this->attributes) return null;
        if ($namespace === '') $namespace = null;
        foreach ($this->attributes as $i => $attr) {
            if ($attr->localName === $localName && $attr->namespaceURI === $namespace) {
                array_splice($this->attributes, $i, 1);
                $attr->parent = null;
                return $attr;
            }
        }
        return null;
    }

    /**
     * @see https://dom.spec.whatwg.org/#dom-element-toggleattribute
     * @throws InvalidCharacterError|NotFoundError
     */
    public function toggleAttribute(string $qualifiedName, bool $force = null): bool
    {
        if (!QName::isValidName($qualifiedName)) {
            throw new InvalidCharacterError();
        }
        if ($this->isHTML && $this->document?->isHTML) {
            $qualifiedName = strtolower($qualifiedName);
        }
        $attr = $this->getAttributeNode($qualifiedName);
        if (!$attr) {
            // If force is not given or is true, create an attribute whose local name is qualifiedName,
            // value is the empty string, and node document is this’s node document,
            // then append this attribute to this, and then return true.
            if ($force || $force === null) {
                $attr = new Attr($qualifiedName);
                $attr->document = $this->document;
                $attr->parent = $this;
                $this->attributes[] = $attr;
                return true;
            }
            return false;
        }
        // 5. Otherwise, if force is not given or is false,
        // remove an attribute given qualifiedName and this, and then return false.
        if (!$force) {
            $this->removeAttributeNode($attr);
            return false;
        }
        // 6.
        return true;
    }

    public function hasAttribute(string $qualifiedName): bool
    {
        return $this->getAttributeNode($qualifiedName) !== null;
    }

    public function hasAttributeNS(?string $namespace, string $localName): bool
    {
        return $this->getAttributeNodeNS($namespace, $localName) !== null;
    }

    /**
     * https://dom.spec.whatwg.org/#concept-element-attributes-get-by-name
     */
    public function getAttributeNode(string $qualifiedName): ?Attr
    {
        if (!$this->attributes) return null;

        if ($this->isHTML && $this->document?->isHTML) {
            $qualifiedName = strtolower($qualifiedName);
        }
        foreach ($this->attributes as $attribute) {
            if ($attribute->name === $qualifiedName) {
                return $attribute;
            }
        }
        return null;
    }

    /**
     * https://dom.spec.whatwg.org/#concept-element-attributes-get-by-namespace
     */
    public function getAttributeNodeNS(?string $namespace, string $localName): ?Attr
    {
        if (!$this->attributes) return null;
        if ($namespace === '') $namespace = null;
        foreach ($this->attributes as $attribute) {
            if ($attribute->localName === $localName && $attribute->namespaceURI === $namespace) {
                return $attribute;
            }
        }
        return null;
    }

    /**
     * @throws InUseAttributeError
     */
    public function setAttributeNode(Attr $attr): ?Attr
    {
        if ($attr->parent && $attr->parent !== $this) {
            throw new InUseAttributeError();
        }
        foreach ($this->attributes as $i => $oldAttr) {
            if ($oldAttr->localName === $attr->localName && $oldAttr->namespaceURI === $attr->namespaceURI) {
                if ($oldAttr === $attr) return $attr;
                $attr->document = $this->document;
                $attr->parent = $this;
                $this->attributes[$i] = $attr;
                return $oldAttr;
            }
        }
        $attr->document = $this->document;
        $attr->parent = $this;
        $this->attributes[] = $attr;
        return null;
    }

    /**
     * @throws InUseAttributeError
     */
    public function setAttributeNodeNS(Attr $attr): ?Attr
    {
        return $this->setAttributeNode($attr);
    }

    /**
     * https://dom.spec.whatwg.org/#dom-element-removeattributenode
     * https://dom.spec.whatwg.org/#concept-element-attributes-remove
     * @throws NotFoundError
     */
    public function removeAttributeNode(Attr $attribute): Attr
    {
        if (!$this->attributes) {
            throw new NotFoundError();
        }

        $index = array_search($attribute, $this->attributes, true);
        if ($index === false) {
            throw new NotFoundError();
        }

        array_splice($this->attributes, $index, 1);
        $attribute->parent = null;

        return $attribute;
    }

    public function getInnerHTML(): string
    {
        // https://w3c.github.io/DOM-Parsing/#the-innerhtml-mixin
        $serializer = new Serializer();
        return $serializer->serialize($this);
    }

    public function setInnerHTML(string $html): void
    {
        // https://w3c.github.io/DOM-Parsing/#the-innerhtml-mixin
        $parser = new Parser();
        $children = $parser->parseFragment($this, $html, $this->document?->encoding ?? 'utf-8');
        $this->replaceChildren(...$children);
    }

    public function getOuterHTML(): string
    {
        // https://w3c.github.io/DOM-Parsing/#dom-element-outerhtml
        $serializer = new Serializer();
        return $serializer->serializeElement($this);
    }

    public function setOuterHTML(string $html): void
    {
        // https://w3c.github.io/DOM-Parsing/#dom-element-outerhtml
        $parent = $this->parent;
        if (!$parent) return;
        if ($parent->nodeType === Node::DOCUMENT_NODE) {
            throw new NoModificationAllowed(sprintf(
                'Failed to execute %s: The element has no parent.',
                __METHOD__,
            ));
        }
        if ($parent->nodeType === Node::DOCUMENT_FRAGMENT_NODE) {
            $parent = new Element('body', Namespaces::HTML);
            $parent->document = $this->document;
        }
        $parser = new Parser();
        $children = $parser->parseFragment($parent, $html, $this->document?->encoding ?? 'utf-8');
        $this->replaceWith(...$children);
    }

    public function insertAdjacentHTML(string $position, string $html): void
    {
        // https://w3c.github.io/DOM-Parsing/#dom-element-insertadjacenthtml
        $position = strtolower($position);
        $context = match ($position) {
            'beforebegin', 'afterend' => $this->parent,
            'afterbegin', 'beforeend' => $this,
            default => throw new SyntaxError(sprintf(
                'Failed to execute %s: The value provided ("%s") is not one of "beforebegin", "afterend", "afterbegin", or "beforeend".',
                __METHOD__,
                $position
            )),
        };
        if (!$context || $context === $this->document) {
            throw new NoModificationAllowed(sprintf(
                'Failed to execute %s: The element has no parent.',
                __METHOD__,
            ));
        }
        if ($context->nodeType !== Node::ELEMENT_NODE || (
            $context->document?->isHTML
            && $context->isHTML
            && $context->localName === 'html'
        )) {
            $context = new Element('body', Namespaces::HTML);
            $context->document = $this->document;
        }
        $parser = new Parser();
        $children = $parser->parseFragment($context, $html, $this->document?->encoding ?? 'utf-8');
        match ($position) {
            'beforebegin' => $this->before(...$children),
            'afterbegin' => $this->prepend(...$children),
            'beforeend' => $this->append(...$children),
            'afterend' => $this->after(...$children),
        };
    }

    /**
     * @see https://dom.spec.whatwg.org/#dom-node-lookupprefix
     */
    public function lookupPrefix(?string $namespace): ?string
    {
        if (!$namespace) return null;
        return $this->locateNamespacePrefix($namespace);
    }

    /**
     * @see https://dom.spec.whatwg.org/#locate-a-namespace
     */
    protected function locateNamespace(?string $prefix): ?string
    {
        if ($this->namespaceURI && $this->prefix === $prefix) {
            return $this->namespaceURI;
        }
        foreach ($this->attributes as $attr) {
            if ($attr->prefix === 'xmlns' && $attr->namespaceURI === Namespaces::XMLNS && $attr->localName === $prefix) {
                return $attr->localName;
            } else if (
                $prefix === null
                && $attr->localName === 'xmlns' && !$attr->prefix && $attr->namespaceURI === Namespaces::XMLNS
            ) {
                return $attr->value ?: null;
            }
        }
        if ($this->parent?->nodeType === Node::ELEMENT_NODE) {
            return $this->parent->locateNamespace($prefix);
        }
        return null;
    }

    /**
     * @see https://dom.spec.whatwg.org/#locate-a-namespace-prefix
     */
    protected function locateNamespacePrefix(string $namespace): ?string
    {
        if ($this->prefix && $this->namespaceURI === $namespace) {
            return $this->prefix;
        }
        foreach ($this->attributes as $attr) {
            if ($attr->prefix === 'xmlns' && $attr->value === $namespace) {
                return $attr->localName;
            }
        }
        if ($this->parent?->nodeType === Node::ELEMENT_NODE) {
            return $this->parent->locateNamespacePrefix($namespace);
        }
        return null;
    }

    protected function clone(?Document $document, bool $deep = false): static
    {
        $copy = new self($this->localName, $this->namespaceURI, $this->prefix);
        $copy->document = $document ?? $this->document;
        foreach ($this->attributes as $attr) {
            $copyAttribute = $attr->clone($copy->document);
            $copyAttribute->parent = $copy;
            $copy->attributes[] = $copyAttribute;
        }
        if ($deep) {
            for ($child = $this->first; $child; $child = $this->next) {
                $childCopy = $child->clone($copy->document, true);
                $copy->uncheckedAppendChild($childCopy);
            }
        }
        return $copy;
    }
}
