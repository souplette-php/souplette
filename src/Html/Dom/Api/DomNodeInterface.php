<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom\Api;

use DOMDocument;
use DOMNamedNodeMap;
use DOMNode;
use DOMNodeList;

/**
 * @property-read string $nodeName
 * @property string $nodeValue
 * @property-read int $nodeType
 * @property-read DOMNode $parentNode
 * @property-read DOMNodeList $childNodes
 * @property-read DOMNode $firstChild
 * @property-read DOMNode $lastChild
 * @property-read DOMNode $previousSibling
 * @property-read DOMNode $nextSibling
 * @property-read DOMNamedNodeMap $attributes
 * @property-read DOMDocument $ownerDocument
 * @property-read string $namespaceURI
 * @property string $prefix
 * @property-read string $localName
 * @property-read string $baseURI
 * @property string $textContent
 */
interface DomNodeInterface
{
    public function insertBefore(DOMNode $newNode, DOMNode $refNode = null);
    public function replaceChild(DOMNode $newNode, DOMNode $oldNode);
    public function removeChild(DOMNode $oldNode);
    public function appendChild(DOMNode $newNode);
    public function hasChildNodes();
    public function cloneNode(bool $deep = false);
    public function normalize();
    public function isSupported(string $feature, string $version);
    public function hasAttributes();
    //public function compareDocumentPosition(DOMNode $other);
    public function isSameNode(DOMNode $node);
    public function lookupPrefix(string $namespace);
    public function isDefaultNamespace(string $namespace);
    public function lookupNamespaceUri(?string $prefix);
    public function getNodePath();
    public function getLineNo();
    public function C14N(bool $exclusive = false, bool $withComments = false, ?array $xpath = null, ?array $nsPrefixes = null);
    public function C14NFile(string $uri, bool $exclusive = false, bool $withComments = false, ?array $xpath = null, ?array $nsPrefixes = null);
}
