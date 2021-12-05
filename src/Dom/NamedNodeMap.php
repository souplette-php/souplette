<?php declare(strict_types=1);

namespace Souplette\Dom;

use Souplette\Dom\Exception\InUseAttributeError;

final class NamedNodeMap
{
    private Element $element;

    /**
     * @var Attr[]
     */
    private array $attributes = [];

    public function getLength(): int
    {
        return 0;
    }

    public function item(int $index): ?Attr
    {
        return $this->attributes[$index] ?? null;
    }

    /**
     * https://dom.spec.whatwg.org/#concept-element-attributes-get-by-name
     */
    public function getNamedItem(string $qualifiedName): ?Attr
    {
        $document = $this->element->ownerDocument;
        if ($this->element->isHTML && $document && $document->isHTML) {
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
    public function getNamedItemNS(?string $namespace, string $localName): ?Attr
    {
        if ($namespace === '') $namespace = null;
        foreach ($this->attributes as $attribute) {
            if ($attribute->name === $localName && $attribute->namespaceURI === $namespace) {
                return $attribute;
            }
        }
        return null;
    }

    /**
     * https://dom.spec.whatwg.org/#concept-element-attributes-set
     * @throws InUseAttributeError
     */
    public function setNamedItem(Attr $attr): ?Attr
    {
        // 1. If attr’s element is neither null nor element, throw an "InUseAttributeError" DOMException.
        $ownerElement = $attr->getOwnerElement();
        if ($ownerElement && $ownerElement !== $this->element) {
            throw new InUseAttributeError();
        }
        // 2. Let oldAttr be the result of getting an attribute given attr’s namespace, attr’s local name, and element.
        $oldAttr = $this->getNamedItemNS($attr->namespaceURI, $attr->localName);
        // 3. If oldAttr is attr, return attr.
        if ($oldAttr === $attr) return $attr;
        // 4. If oldAttr is non-null, then replace oldAttr with attr.
        if ($oldAttr) {

        } else {
            // 5. Otherwise, append attr to element.
        }
        // 6. Return oldAttr.
        return $oldAttr;
    }

    /**
     * @throws InUseAttributeError
     */
    public function setNamedItemNS(Attr $attr): ?Attr
    {
        return $this->setNamedItem($attr);
    }

    /**
     * https://dom.spec.whatwg.org/#concept-element-attributes-remove-by-name
     */
    public function removeNamedItem(string $qualifiedName): Attr
    {
        // 1. Let attr be the result of getting an attribute given qualifiedName and element.
        $attr = $this->getNamedItem($qualifiedName);
        // 2. If attr is non-null, then remove attr.
        if ($attr) {
            $this->removeAttribute($attr);
        }
        // 3. Return attr.
        return $attr;
    }

    /**
     * https://dom.spec.whatwg.org/#concept-element-attributes-remove-by-namespace
     */
    public function removeNamedItemNS(?string $namespace, string $localName): Attr
    {
        // 1. Let attr be the result of getting an attribute given namespace, localName, and element.
        $attr = $this->getNamedItemNS($namespace, $localName);
        // 2. If attr is non-null, then remove attr.
        if ($attr) {
            $this->removeAttribute($attr);
        }
        // 3. Return attr.
        return $attr;
    }

    private function removeAttribute(Attr $attr)
    {
        // 1. Handle attribute changes for attribute with attribute’s element, attribute’s value, and null.
        // 2. Remove attribute from attribute’s element’s attribute list.
        // 3. Set attribute’s element to null.
    }
}
