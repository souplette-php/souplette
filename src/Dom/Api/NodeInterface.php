<?php declare(strict_types=1);

namespace Souplette\Dom\Api;


use Souplette\Dom\Exception\DomException;
use Souplette\Dom\Exception\HierarchyRequestError;
use Souplette\Dom\Node\Document;
use Souplette\Dom\Node\Element;
use Souplette\Dom\Node\Node;
use Souplette\Dom\Node\ParentNode;

/**
 * @see https://dom.spec.whatwg.org/#interface-node
 *
 * @property-read int $nodeType
 * @property-read string $nodeName
 *
 * @property-read string $baseURI
 *
 * @property bool $isConnected
 * @property-read ?Document $ownerDocument
 * @property-read ?ParentNode $parentNode
 * @property-read ?Element $parentElement
 * @property-read Node[] $childNodes
 * @property-read ?Node $firstChild
 * @property-read ?Node $lastChild
 * @property-read ?Node $previousSibling
 * @property-read ?Node $nextSibling
 *
 * @property-read ?string $nodeValue
 * @property ?string $textContent
 */
interface NodeInterface
{
    public function isConnected(): bool;
    public function getOwnerDocument(): ?Document;
    public function getRootNode(array $options = []): Node;
    public function getParentNode(): ?Node;
    public function getParentElement(): ?Element;
    public function hasChildNodes(): bool;
    public function getChildNodes(): array;
    public function getFirstChild(): ?Node;
    public function getLastChild(): ?Node;
    public function getPreviousSibling(): ?Node;
    public function getNextSibling(): ?Node;

    public function getNodeValue(): ?string;
    public function setNodeValue(string $value): void;
    public function getTextContent(): ?string;
    public function setTextContent(string $value): void;
    public function normalize(): void;

    public function cloneNode(bool $deep = false): static;
    public function isEqualNode(?Node $otherNode): bool;
    public function isSameNode(?Node $otherNode): bool;

    public function compareDocumentPosition(Node $other): int;
    public function contains(?Node $other): bool;

    public function lookupPrefix(?string $namespace): ?string;
    public function lookupNamespaceURI(?string $prefix): ?string;
    public function isDefaultNamespace(?string $namespace): bool;

    /**
     * @throws DomException
     */
    public function insertBefore(Node $node, ?Node $child = null): Node;
    /**
     * @throws DomException
     */
    public function appendChild(Node $node): Node;
    /**
     * @throws DomException
     */
    public function replaceChild(Node $node, Node $child): Node;
    /**
     * @throws DomException
     */
    public function removeChild(Node $child): Node;
}
