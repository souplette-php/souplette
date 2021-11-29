<?php declare(strict_types=1);

namespace Souplette\Dom\Api\Native;

use DOMDocument;
use DOMNamedNodeMap;
use DOMNode;
use DOMNodeList;

/**
 * @property-read string $nodeName
 * @property string $nodeValue
 * @property-read int $nodeType
 * @property-read DOMNode|null $parentNode
 * @property-read DOMNodeList $childNodes
 * @property-read DOMNode|null $firstChild
 * @property-read DOMNode|null $lastChild
 * @property-read DOMNode|null $previousSibling
 * @property-read DOMNode|null $nextSibling
 * @property-read DOMNamedNodeMap|null $attributes
 * @property-read DOMDocument|null $ownerDocument
 * @property-read string|null $namespaceURI
 * @property string $prefix
 * @property-read string $localName
 * @property-read string|null $baseURI
 * @property string $textContent
 */
interface DomNodeInterface
{
    /**
     * @return DOMNode|false
     */
    public function appendChild(DOMNode $node);
    public function C14N(bool $exclusive = false, bool $withComments = false, ?array $xpath = null, ?array $nsPrefixes = null): string|false;
    public function C14NFile(string $uri, bool $exclusive = false, bool $withComments = false, ?array $xpath = null, ?array $nsPrefixes = null): int|false;
    /**
     * @return DOMNode|false
     */
    public function cloneNode(bool $deep = false);
    public function getLineNo(): int;
    public function getNodePath(): ?string;
    public function hasAttributes(): bool;
    public function hasChildNodes(): bool;
    /**
     * @return DOMNode|false
     */
    public function insertBefore(DOMNode $node, ?DOMNode $child = null);
    public function isDefaultNamespace(string $namespace): bool;
    public function isSameNode(DOMNode $otherNode): bool;
    public function isSupported(string $feature, string $version): bool;
    public function lookupNamespaceUri(string $prefix): ?string;
    public function lookupPrefix(string $namespace): ?string;
    public function normalize(): void;
    /**
     * @return DOMNode|false
     */
    public function removeChild(DOMNode $child);
    /**
     * @return DOMNode|false
     */
    public function replaceChild(DOMNode $node, DOMNode $child);
}
