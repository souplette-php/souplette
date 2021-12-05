<?php declare(strict_types=1);

namespace Souplette\Dom;

use Souplette\Css\Selectors\SelectorQuery;
use Souplette\Dom\Api\ChildNodeInterface;
use Souplette\Dom\Api\NonDocumentTypeChildNodeInterface;
use Souplette\Dom\Exception\InvalidCharacterError;
use Souplette\Dom\Exception\NamespaceError;
use Souplette\Dom\Exception\NoModificationAllowed;
use Souplette\Dom\Exception\NotFoundError;
use Souplette\Dom\Exception\SyntaxError;
use Souplette\Dom\Internal\TokenList;
use Souplette\Dom\Traits\ChildNodeTrait;
use Souplette\Dom\Traits\NonDocumentTypeChildNodeTrait;
use Souplette\Html\Parser;
use Souplette\Html\Serializer;
use Souplette\Xml\QName;

/**
 * @property string $id
 * @property string $className
 * @property string $innerHTML
 * @property string $outerHTML
 * @property-read TokenList $classList
 */
class Element extends ParentNode implements ChildNodeInterface, NonDocumentTypeChildNodeInterface
{
    use ChildNodeTrait;
    use NonDocumentTypeChildNodeTrait;

    public readonly int $nodeType;
    public readonly string $nodeName;
    public readonly string $localName;
    public readonly string $tagName;
    public readonly ?string $namespaceURI;
    public readonly ?string $prefix;
    public readonly bool $isHTML;

    /**
     * @var Attr[]
     */
    protected array $attributeList = [];
    protected TokenList $classList;

    public function __construct(string $localName, ?string $namespace = null, ?string $prefix = null)
    {
        $this->nodeType = Node::ELEMENT_NODE;
        $this->localName = $localName;
        $this->namespaceURI = $namespace;
        $this->prefix = $prefix;
        $this->isHTML = $namespace === Namespaces::HTML;
        if ($this->isHTML) {
            $this->tagName = strtoupper($localName);
        } else {
            $this->tagName = $prefix ? "{$prefix}:{$localName}" : $localName;
        }
        $this->nodeName = $this->tagName;
    }

    public function __get(string $prop)
    {
        return match ($prop) {
            'attributes' => $this->attributeList,
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

    public function getElementsByClassName(string $classNames): array
    {
        return SelectorQuery::byClassNames($this, $classNames);
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
        foreach ($this->attributeList as $attribute) {
            $otherAttr = $otherNode->getAttributeNS($attribute->namespaceURI, $attribute->localName);
            if (!$attribute->isEqualNode($otherAttr)) {
                return false;
            }
        }
        return $this->areChildrenEqual($otherNode);
    }

    public function cloneNode(bool $deep = false): static
    {
        $copy = new self($this->localName, $this->namespaceURI, $this->prefix);
        $copy->document = $this->document;
        foreach ($this->attributeList as $attr) {
            $copyAttribute = $attr->cloneNode();
            $copyAttribute->parent = $copy;
            $copy->attributeList[] = $copyAttribute;
        }
        if ($deep) {
            for ($child = $this->first; $child; $child = $this->next) {
                $childCopy = $child->cloneNode(true);
                $copy->adopt($childCopy);
                $copy->uncheckedAppendChild($childCopy);
            }
        }
        return $copy;
    }

    /**
     * @return string[]
     */
    public function getAttributeNames(): array
    {
        if (!$this->attributeList) return [];
        return array_map(fn(Attr $attr) => $attr->name, $this->attributeList);
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
        $attribute = null;
        foreach ($this->attributeList as $attr) {
            if ($attr->name === $qualifiedName) {
                $attribute = $attr;
                break;
            }
        }
        if (!$attribute) {
            $attribute = new Attr($qualifiedName);
            $attribute->document = $this->document;
            $attribute->parent = $this;
            $attribute->value = $value;
            $this->attributeList[] = $attribute;
            return;
        }
        $attribute->value = $value;
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
            $this->attributeList[] = $attribute;
            return;
        }
        $attribute->value = $value;
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
     * @see https://dom.spec.whatwg.org/#dom-element-toggleattribute
     * @throws InvalidCharacterError
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
                $this->attributeList[] = $attr;
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

    /**
     * https://dom.spec.whatwg.org/#concept-element-attributes-get-by-name
     */
    public function getAttributeNode(string $qualifiedName): ?Attr
    {
        if (!$this->attributeList) return null;

        if ($this->isHTML && $this->document?->isHTML) {
            $qualifiedName = strtolower($qualifiedName);
        }
        foreach ($this->attributeList as $attribute) {
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
        if (!$this->attributeList) return null;
        if ($namespace === '') $namespace = null;
        foreach ($this->attributeList as $attribute) {
            if ($attribute->localName === $localName && $attribute->namespaceURI === $namespace) {
                return $attribute;
            }
        }
        return null;
    }

    public function removeAttribute(string $qualifiedName): ?Attr
    {
        if (!$this->attributeList) return null;
        // 1. Let attr be the result of getting an attribute given qualifiedName and element.
        if ($this->isHTML && $this->document?->isHTML) {
            $qualifiedName = strtolower($qualifiedName);
        }
        foreach ($this->attributeList as $i => $attribute) {
            if ($attribute->name === $qualifiedName) {
                array_splice($this->attributeList, $i, 1);
                $attribute->parent = null;
                return $attribute;
            }
        }
        return null;
    }

    public function removeAttributeNS(?string $namespace, string $localName): ?Attr
    {
        if (!$this->attributeList) return null;
        if ($namespace === '') $namespace = null;
        foreach ($this->attributeList as $i => $attribute) {
            if ($attribute->localName === $localName && $attribute->namespaceURI === $namespace) {
                array_splice($this->attributeList, $i, 1);
                $attribute->parent = null;
                return $attribute;
            }
        }
        return null;
    }

    /**
     * https://dom.spec.whatwg.org/#dom-element-removeattributenode
     * https://dom.spec.whatwg.org/#concept-element-attributes-remove
     */
    public function removeAttributeNode(Attr $attribute): Attr
    {
        if (!$this->attributeList) {
            throw new NotFoundError();
        }

        $index = array_search($attribute, $this->attributeList, true);
        if ($index === false) {
            throw new NotFoundError();
        }

        array_splice($this->attributeList, $index, 1);
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
        foreach ($this->attributeList as $attr) {
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
        foreach ($this->attributeList as $attr) {
            if ($attr->prefix === 'xmlns' && $attr->value === $namespace) {
                return $attr->localName;
            }
        }
        if ($this->parent?->nodeType === Node::ELEMENT_NODE) {
            return $this->parent->locateNamespacePrefix($namespace);
        }
        return null;
    }
}
