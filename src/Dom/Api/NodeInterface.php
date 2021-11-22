<?php declare(strict_types=1);

namespace Souplette\Dom\Api;

use DOMElement;
use DOMNode;

/**
 * @see https://dom.spec.whatwg.org/#interface-node
 *
 * @property-read DOMElement|null $parentElement
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

    public function compareDocumentPosition(?DOMNode $other): int;

    public function contains(?DOMNode $other): bool;

    public function getParentElement(): ?DOMElement;

    public function getRootNode(): DOMNode;
}
