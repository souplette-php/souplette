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
    public function cloneNode($deep = null);
    public function normalize();
    public function isSupported($feature, $version);
    public function hasAttributes();
    public function compareDocumentPosition(DOMNode $other);
    public function isSameNode(DOMNode $node);
    public function lookupPrefix($namespaceURI);
    public function isDefaultNamespace($namespaceURI);
    public function lookupNamespaceUri($prefix);
    public function isEqualNode(DOMNode $arg);
    public function getFeature($feature, $version);
    public function setUserData($key, $data, $handler);
    public function getUserData($key);
    public function getNodePath();
    public function getLineNo();
    public function C14N($exclusive = null, $with_comments = null, ?array $xpath = null, ?array $ns_prefixes = null);
    public function C14NFile($uri, $exclusive = null, $with_comments = null, ?array $xpath = null, ?array $ns_prefixes = null);
}
