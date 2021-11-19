<?php declare(strict_types=1);

namespace Souplette\Html\Dom\Api;

use DOMAttr;

/**
 * @property-read bool $schemaTypeInfo
 * @property-read string $tagName
 */
interface DomElementInterface extends DomNodeInterface
{
    public function hasAttribute(string $qualifiedName);
    public function getAttribute(string $qualifiedName);
    public function setAttribute(string $qualifiedName, string $value): DOMAttr|false;
    public function removeAttribute(string $qualifiedName): bool;
    public function getAttributeNode(string $qualifiedName);
    public function setAttributeNode(DOMAttr $attr);
    // FIXME: waiting for https://bugs.php.net/bug.php?id=80537 to be fixed
    //public function removeAttributeNode(DOMAttr $oldNode);
    public function getElementsByTagName(string $qualifiedName);
    // FIXME: waitinf for https://bugs.php.net/bug.php?id=80599
    //public function hasAttributeNS(?string $namespace, string $localName);
    //public function getAttributeNS(?string $namespace, string $localName);
    //public function setAttributeNS(?string $namespace, string $qualifiedName, string $value);
    //public function removeAttributeNS(?string $namespace, string $localName);
    //public function getAttributeNodeNS(?string $namespace, string $localName);
    public function setAttributeNodeNS(DOMAttr $attr);
    public function getElementsByTagNameNS(string $namespace, string $localName);
    public function setIdAttribute(string $qualifiedName, bool $isId);
    public function setIdAttributeNS(string $namespace, string $qualifiedName, bool $isId);
    public function setIdAttributeNode(DOMAttr $attr, bool $isId);
}
