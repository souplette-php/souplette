<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom\Api;

use DOMAttr;
use DOMNodeList;
use JoliPotage\Html\Dom\TokenList;

/**
 * @property string $innerHTML
 * @property string $outerHTML
 * @property-read TokenList $classList
 */
interface HtmlElementInterface extends HtmlNodeInterface
{
    public function getInnerHTML(): string;
    public function setInnerHTML(string $html): void;
    public function getOuterHTML(): string;
    public function setOuterHTML(string $html): void;
    public function getClassList(): TokenList;
    public function getElementsByClassName(string $classNames): DOMNodeList;

    // DOMElement methods
    public function getAttribute($name);
    public function setAttribute($name, $value);
    public function getAttributeNode($name);
    public function setAttributeNode(DOMAttr $attr);
    public function removeAttributeNode(DOMAttr $attr);
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
