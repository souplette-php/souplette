<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom\Api;

use DOMElement;
use DOMNode;
use DOMNodeList;

/**
 * @see https://dom.spec.whatwg.org/#document
 *
 * @property-read string $mode
 * @property-read string $compatMode
 * @property-read DOMElement $head
 * @property-read DOMElement $body
 * @property string $title
 */
interface HtmlDocumentInterface extends HtmlNodeInterface
{
    public function getMode(): string;
    public function getCompatMode(): string;
    public function getHead(): ?DOMElement;
    public function getBody(): ?DOMElement;
    public function getTitle(): string;
    public function setTitle(string $title): void;
    public function getElementsByClassName(string $classNames): DOMNodeList;

    public function createElement($name, $value = null);
    public function createElementNS($namespaceURI, $qualifiedName, $value = null);
    public function createDocumentFragment();
    public function createTextNode($content);
    public function createComment($data);
    public function createCDATASection($data);
    public function createProcessingInstruction($target, $data = null);
    public function createAttribute($name);
    public function importNode(DOMNode $importedNode, $deep = null);
    public function createAttributeNS($namespaceURI, $qualifiedName);
    public function getElementById($elementId);
    public function adoptNode(DOMNode $source);
    public function registerNodeClass($baseClass, $extendedClass);
}
