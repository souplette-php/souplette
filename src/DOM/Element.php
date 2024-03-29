<?php declare(strict_types=1);

namespace Souplette\DOM;

use Souplette\CSS\Selectors\SelectorQuery;
use Souplette\DOM\Api\ChildNodeInterface;
use Souplette\DOM\Api\NonDocumentTypeChildNodeInterface;
use Souplette\DOM\Collections\TokenList;
use Souplette\DOM\Exception\InUseAttributeError;
use Souplette\DOM\Exception\InvalidCharacterError;
use Souplette\DOM\Exception\NamespaceError;
use Souplette\DOM\Exception\NotFoundError;
use Souplette\DOM\Internal\NodeFlags;
use Souplette\DOM\Traits\ChildNodeTrait;
use Souplette\DOM\Traits\DOMParsingTrait;
use Souplette\DOM\Traits\GetElementsByClassNameTrait;
use Souplette\DOM\Traits\GetElementsByTagNameTrait;
use Souplette\DOM\Traits\NonDocumentTypeChildNodeTrait;
use Souplette\DOM\Traits\SanitizerApiTrait;
use Souplette\XML\QName;

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
    use DOMParsingTrait;
    use SanitizerApiTrait;

    public readonly int $nodeType;
    public readonly string $nodeName;
    public readonly string $localName;
    public readonly string $qualifiedName;
    public readonly string $tagName;
    public readonly ?string $namespaceURI;
    public readonly ?string $prefix;
    public readonly bool $isHTML;

    /**
     * @internal
     * @var Attr[]
     */
    public array $_attrs = [];
    protected ?TokenList $classList = null;

    public function __construct(string $localName, ?string $namespace = null, ?string $prefix = null)
    {
        $this->nodeType = Node::ELEMENT_NODE;
        $this->localName = $localName;
        $this->namespaceURI = $namespace ?: null;
        $this->prefix = $prefix ?: null;
        $this->qualifiedName = $prefix ? "{$prefix}:{$localName}" : $localName;
        $this->_flags |= NodeFlags::IS_CONTAINER;
        $this->_flags |= match ($namespace) {
            Namespaces::HTML => NodeFlags::NS_TYPE_HTML,
            Namespaces::SVG => NodeFlags::NS_TYPE_SVG,
            Namespaces::MATHML => NodeFlags::NS_TYPE_MATHTML,
            default => NodeFlags::NS_TYPE_OTHER,
        };
        $this->isHTML = ($this->_flags & NodeFlags::ELEMENT_NS_MASK) === NodeFlags::NS_TYPE_HTML;
        //$this->isHTML = $namespace === Namespaces::HTML;
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
            'attributes' => $this->_attrs,
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
        if ($otherNode === $this) return true;
        if (!$otherNode
            || $this->nodeType !== $otherNode->nodeType
            || $this->localName !== $otherNode->localName
            || $this->prefix !== $otherNode->prefix
            || $this->namespaceURI !== $otherNode->namespaceURI
            || \count($this->_attrs) !== \count($otherNode->_attrs)
        ) {
            return false;
        }
        foreach ($this->_attrs as $attr) {
            $otherAttr = $otherNode->getAttributeNodeNS($attr->namespaceURI, $attr->localName);
            if (!$otherAttr || !$attr->isEqualNode($otherAttr)) {
                return false;
            }
        }
        return $this->areChildrenEqual($otherNode);
    }

    public function getAttributes(): array
    {
        return $this->_attrs;
    }

    /**
     * @return string[]
     */
    public function getAttributeNames(): array
    {
        if (!$this->_attrs) return [];
        return array_map(fn(Attr $attr) => $attr->name, $this->_attrs);
    }

    public function getAttribute(string $qualifiedName): ?string
    {
        return $this->getAttributeNode($qualifiedName)?->_value;
    }

    public function getAttributeNS(?string $namespace, string $localName): ?string
    {
        return $this->getAttributeNodeNS($namespace, $localName)?->_value;
    }

    public function setAttribute(string $qualifiedName, string $value): void
    {
        if (!QName::isValidName($qualifiedName)) {
            throw new InvalidCharacterError();
        }
        if ($this->isHTML && $this->_doc?->isHTML) {
            $qualifiedName = strtolower($qualifiedName);
        }
        foreach ($this->_attrs as $attr) {
            if ($attr->name === $qualifiedName) {
                $this->didModifyAttribute($qualifiedName, $attr->_value, $attr->_value = $value);
                return;
            }
        }
        $attr = new Attr($qualifiedName);
        $attr->_doc = $this->_doc;
        $attr->_parent = $this;
        $attr->_value = $value;
        $this->_attrs[] = $attr;
        $this->didAddAttribute($qualifiedName, $value);
    }

    /**
     * @throws InvalidCharacterError
     * @throws NamespaceError
     */
    public function setAttributeNS(?string $namespace, string $qualifiedName, string $value): void
    {
        [$namespace, $prefix, $localName] = QName::validateAndExtract($qualifiedName, $namespace);
        $attr = $this->getAttributeNodeNS($namespace, $localName);
        if (!$attr) {
            $attr = new Attr($localName, $namespace, $prefix);
            $attr->_doc = $this->_doc;
            $attr->_parent = $this;
            $attr->_value = $value;
            $this->_attrs[] = $attr;
            $this->didAddAttribute($qualifiedName, $value);
            return;
        }
        $this->didModifyAttribute($qualifiedName, $attr->_value, $attr->_value = $value);
    }

    public function removeAttribute(string $qualifiedName): ?Attr
    {
        if (!$this->_attrs) return null;
        // 1. Let attr be the result of getting an attribute given qualifiedName and element.
        if ($this->isHTML && $this->_doc?->isHTML) {
            $qualifiedName = strtolower($qualifiedName);
        }
        foreach ($this->_attrs as $i => $attr) {
            if ($attr->name === $qualifiedName) {
                array_splice($this->_attrs, $i, 1);
                $attr->_parent = null;
                $this->didRemoveAttribute($qualifiedName, $attr->_value);
                return $attr;
            }
        }
        return null;
    }

    public function removeAttributeNS(?string $namespace, string $localName): ?Attr
    {
        if (!$this->_attrs) return null;
        if ($namespace === '') $namespace = null;
        foreach ($this->_attrs as $i => $attr) {
            if ($attr->localName === $localName && $attr->namespaceURI === $namespace) {
                array_splice($this->_attrs, $i, 1);
                $attr->_parent = null;
                $this->didRemoveAttribute($attr->name, $attr->_value);
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
        if ($this->isHTML && $this->_doc?->isHTML) {
            $qualifiedName = strtolower($qualifiedName);
        }
        $attr = $this->getAttributeNode($qualifiedName);
        if (!$attr) {
            // If force is not given or is true, create an attribute whose local name is qualifiedName,
            // value is the empty string, and node document is this’s node document,
            // then append this attribute to this, and then return true.
            if ($force || $force === null) {
                $attr = new Attr($qualifiedName);
                $attr->_doc = $this->_doc;
                $attr->_parent = $this;
                $this->_attrs[] = $attr;
                $this->didAddAttribute($qualifiedName, '');
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
        if (!$this->_attrs) return null;

        if ($this->isHTML && $this->_doc?->isHTML) {
            $qualifiedName = strtolower($qualifiedName);
        }
        foreach ($this->_attrs as $attribute) {
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
        if (!$this->_attrs) return null;
        if ($namespace === '') $namespace = null;
        foreach ($this->_attrs as $attribute) {
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
        if ($attr->_parent && $attr->_parent !== $this) {
            throw new InUseAttributeError();
        }
        foreach ($this->_attrs as $i => $oldAttr) {
            if ($oldAttr->localName === $attr->localName && $oldAttr->namespaceURI === $attr->namespaceURI) {
                if ($oldAttr === $attr) return $attr;
                $this->didRemoveAttribute($oldAttr->name, $oldAttr->_value);
                $attr->_doc = $this->_doc;
                $attr->_parent = $this;
                $this->_attrs[$i] = $attr;
                $this->didAddAttribute($attr->name, $attr->_value);
                return $oldAttr;
            }
        }
        $attr->_doc = $this->_doc;
        $attr->_parent = $this;
        $this->_attrs[] = $attr;
        $this->didAddAttribute($attr->name, $attr->_value);
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
        if (!$this->_attrs) {
            throw new NotFoundError();
        }

        $index = array_search($attribute, $this->_attrs, true);
        if ($index === false) {
            throw new NotFoundError();
        }

        array_splice($this->_attrs, $index, 1);
        $attribute->_parent = null;
        $this->didRemoveAttribute($attribute->name, $attribute->_value);

        return $attribute;
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
        foreach ($this->_attrs as $attr) {
            if ($attr->prefix === 'xmlns' && $attr->namespaceURI === Namespaces::XMLNS && $attr->localName === $prefix) {
                return $attr->_value ?: null;
            } else if (
                !$prefix && !$attr->prefix
                && $attr->localName === 'xmlns' && $attr->namespaceURI === Namespaces::XMLNS
            ) {
                return $attr->_value ?: null;
            }
        }
        if ($this->_parent?->nodeType === Node::ELEMENT_NODE) {
            return $this->_parent->locateNamespace($prefix);
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
        foreach ($this->_attrs as $attr) {
            if ($attr->prefix === 'xmlns' && $attr->_value === $namespace) {
                return $attr->localName;
            }
        }
        if ($this->_parent?->nodeType === Node::ELEMENT_NODE) {
            return $this->_parent->locateNamespacePrefix($namespace);
        }
        return null;
    }

    protected function clone(?Document $document, bool $deep = false): static
    {
        $copy = new self($this->localName, $this->namespaceURI, $this->prefix);
        $copy->_doc = $document ?? $this->_doc;
        foreach ($this->_attrs as $attr) {
            $copyAttribute = $attr->clone($copy->_doc);
            $copyAttribute->_parent = $copy;
            $copy->_attrs[] = $copyAttribute;
        }
        if ($deep) {
            for ($child = $this->_first; $child; $child = $child->_next) {
                $childCopy = $child->clone($copy->_doc, true);
                $copy->uncheckedAppendChild($childCopy);
            }
        }
        return $copy;
    }

    // ==============================================================
    // Mutation notifications
    // ==============================================================

    protected function insertedInto(ParentNode $insertionPoint): void
    {
        parent::insertedInto($insertionPoint);
        if (!$insertionPoint->hasFlag(NodeFlags::IS_CONNECTED)) {
            return;
        }
        if ($id = $this->getAttribute('id')) {
            $this->updateId(null, $id);
        }
    }

    protected function removedFrom(ParentNode $insertionPoint): void
    {
        $wasInDocument = $insertionPoint->hasFlag(NodeFlags::IS_CONNECTED);
        if ($wasInDocument) {
            if ($id = $this->getAttribute('id')) {
                $this->updateId($id, null);
            }
        }
        parent::removedFrom($insertionPoint);
    }

    protected function didAddAttribute(string $qualifiedName, string $value): void
    {
        match ($qualifiedName) {
            'id' => $this->updateId(null, $value),
            'class' => $this->updateClass(null, $value),
            default => null,
        };
    }

    protected function didModifyAttribute(string $qualifiedName, string $oldValue, string $newValue): void
    {
        match ($qualifiedName) {
            'id' => $this->updateId($oldValue, $newValue),
            'class' => $this->updateClass($oldValue, $newValue),
            default => null,
        };
    }

    protected function didRemoveAttribute(string $qualifiedName, string $oldValue): void
    {
        match ($qualifiedName) {
            'id' => $this->updateId($oldValue, null),
            'class' => $this->updateClass($oldValue, null),
            default => null,
        };
    }

    private function updateId(?string $oldId, ?string $newId): void
    {
        if (!$this->hasFlag(NodeFlags::IS_CONNECTED)) {
            return;
        }
        if ($oldId === $newId) {
            return;
        }
        $treeScope = $this->_doc;
        if ($oldId) {
            $treeScope->removeElementById($oldId, $this);
        }
        if ($newId) {
            $treeScope->addElementById($newId, $this);
        }
    }

    private function updateClass(?string $oldValue, ?string $newValue): void
    {
        if ($this->classList && $newValue !== $oldValue) {
            $this->classList->notifyAttributeChanged($oldValue, $newValue);
        }
    }
}
