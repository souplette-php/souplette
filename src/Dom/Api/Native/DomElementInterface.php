<?php declare(strict_types=1);

namespace Souplette\Dom\Api\Native;

use DOMAttr;
use DOMNameSpaceNode;
use DOMNodeList;

/**
 * @property-read bool $schemaTypeInfo
 * @property-read string $tagName
 */
interface DomElementInterface extends DomNodeInterface
{
    public function getAttribute(string $qualifiedName): string;
    /**
     * @return DOMAttr|DOMNameSpaceNode|false
     */
    public function getAttributeNode(string $qualifiedName);
    /**
     * @return DOMAttr|DOMNameSpaceNode|null
     */
    public function getAttributeNodeNS(?string $namespace, string $localName);
    public function getAttributeNS(?string $namespace, string $localName): string;
    public function getElementsByTagName(string $qualifiedName): DOMNodeList;
    public function getElementsByTagNameNS(?string $namespace, string $localName): DOMNodeList;
    public function hasAttribute(string $qualifiedName): bool;
    public function hasAttributeNS(?string $namespace, string $localName): bool;
    public function removeAttribute(string $qualifiedName): bool;
    /**
     * @return DOMAttr|false
     */
    public function removeAttributeNode(DOMAttr $attr);
    public function removeAttributeNS(?string $namespace, string $localName): void;
    public function setAttribute(string $qualifiedName, string $value): DOMAttr|bool;
    /**
     * @return DOMAttr|false|null
     */
    public function setAttributeNode(DOMAttr $attr);
    /**
     * @return DOMAttr|false|null
     */
    public function setAttributeNodeNS(DOMAttr $attr);
    public function setAttributeNS(?string $namespace, string $qualifiedName, string $value): void;
    public function setIdAttribute(string $qualifiedName, bool $isId): void;
    public function setIdAttributeNode(DOMAttr $attr, bool $isId): void;
    public function setIdAttributeNS(string $namespace, string $qualifiedName, bool $isId): void;
}
