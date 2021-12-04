<?php declare(strict_types=1);

namespace Souplette\Dom\Api;

use DOMNode;
use Souplette\Dom\Api\Native\DomNodeInterface;
use Souplette\Dom\Legacy\Document;
use Souplette\Dom\Legacy\Element;

/**
 * @see https://dom.spec.whatwg.org/#interface-node
 *
 * @property-read Document|null $ownerDocument
 * @property-read Element|null $parentElement
 * @property-read NodeInterface|null $parentNode
 * @property-read NodeInterface|null $firstChild
 * @property-read NodeInterface|null $lastChild
 * @property-read NodeInterface|null $previousSibling
 * @property-read NodeInterface|null $nextSibling
 *
 * @method NodeInterface|false appendChild(DOMNode $node)
 * @method NodeInterface|false cloneNode(bool $deep = false)
 * @method NodeInterface|false insertBefore(DOMNode $node, ?DOMNode $child = null)
 * @method NodeInterface|false removeChild(DOMNode $child)
 * @method NodeInterface|false replaceChild(DOMNode $node, DOMNode $child)
 */
interface NodeInterface extends DomNodeInterface
{
    const ELEMENT_NODE = 1;
    const ATTRIBUTE_NODE = 2;
    const TEXT_NODE = 3;
    const CDATA_SECTION_NODE = 4;
    const ENTITY_REFERENCE_NODE = 5; // historical
    const ENTITY_NODE = 6; // historical
    const PROCESSING_INSTRUCTION_NODE = 7;
    const COMMENT_NODE = 8;
    const DOCUMENT_NODE = 9;
    const DOCUMENT_TYPE_NODE = 10;
    const DOCUMENT_FRAGMENT_NODE = 11;
    const NOTATION_NODE = 12; // historical
    const HTML_DOCUMENT_NODE = 13;

    /**
     * @see https://dom.spec.whatwg.org/#dom-node-comparedocumentposition
     */
    const DOCUMENT_POSITION_DISCONNECTED = 0x01;
    const DOCUMENT_POSITION_PRECEDING = 0x02;
    const DOCUMENT_POSITION_FOLLOWING = 0x04;
    const DOCUMENT_POSITION_CONTAINS = 0x08;
    const DOCUMENT_POSITION_CONTAINED_BY = 0x10;
    const DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC = 0x20;

    public function isEqualNode(?DOMNode $otherNode): bool;

    public function contains(?DOMNode $other): bool;

    /**
     * Returns a bitmask indicating the position of other relative to node.
     * These are the bits that can be set:
     *
     * - `NodeInterface::DOCUMENT_POSITION_DISCONNECTED` (1)
     *   Set when node and other are not in the same tree.
     * - `NodeInterface::DOCUMENT_POSITION_PRECEDING` (2)
     *   Set when other is preceding node.
     * - `NodeInterface::DOCUMENT_POSITION_FOLLOWING` (4)
     *   Set when other is following node.
     * - `NodeInterface::DOCUMENT_POSITION_CONTAINS` (8)
     *   Set when other is an ancestor of node.
     * -` NodeInterface::DOCUMENT_POSITION_CONTAINED_BY` (16, 10 in hexadecimal)
     *   Set when other is a descendant of node.
     *
     * @param DOMNode|null $other
     * @return int
     */
    public function compareDocumentPosition(?DOMNode $other): int;

    public function getParentElement(): ?Element;

    public function getRootNode(): DOMNode;
}
