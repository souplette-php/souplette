<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom\Api;

use DOMAttr;

/**
 * @property-read bool $schemaTypeInfo
 * @property-read string $tagName
 */
interface DomElementInterface extends DomNodeInterface
{
    public function getAttribute($name);
    public function setAttribute($name, $value);
    public function removeAttribute($name);
    public function getAttributeNode($name);
    public function setAttributeNode(DOMAttr $attr);
    public function removeAttributeNode(DOMAttr $oldNode);
    public function getElementsByTagName($name);
    public function getAttributeNS($namespaceURI, $localName);
    public function setAttributeNS($namespaceURI, $qualifiedName, $value);
    public function removeAttributeNS($namespaceURI, $localName);
    public function getAttributeNodeNS($namespaceURI, $localName);
    public function setAttributeNodeNS(DOMAttr $attr);
    public function getElementsByTagNameNS($namespaceURI, $localName);
    public function hasAttribute($name);
    public function hasAttributeNS($namespaceURI, $localName);
    public function setIdAttribute($name, $isId);
    public function setIdAttributeNS($namespaceURI, $localName, $isId);
    public function setIdAttributeNode(DOMAttr $attr, $isId);
}
