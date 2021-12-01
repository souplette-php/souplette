<?php declare(strict_types=1);

namespace Souplette\Dom\Traits;

use DOMElement;
use DOMNode;
use Souplette\Dom\Api\NodeInterface;
use Souplette\Dom\Document;
use Souplette\Dom\Element;
use Souplette\Dom\Internal\DomIdioms;

/**
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
trait NodeTrait
{
    /**
     * @see https://dom.spec.whatwg.org/#dom-node-comparedocumentposition
     */
    public function compareDocumentPosition(?DOMNode $other): int
    {
        // The compareDocumentPosition(other) method, when invoked, must run these steps:
        // 1.If this is other, then return zero.
        if ($this === $other) return 0;
        // 2. Let node1 be other and node2 be this.
        $node1 = $other;
        $node2 = $this;
        // 3. Let attr1 and attr2 be null.
        $attr1 = $attr2 = null;
        // 4. If node1 is an attribute, then set attr1 to node1 and node1 to attr1’s element.
        if ($node1->nodeType === \XML_ATTRIBUTE_NODE) {
            $attr1 = $node1;
            $node1 = $attr1->ownerElement;
        }
        // 5. If node2 is an attribute, then:
        if ($node2->nodeType === \XML_ATTRIBUTE_NODE) {
            // 1. Set attr2 to node2 and node2 to attr2’s element.
            $attr2 = $node2;
            $node2 = $attr2->ownerElement;
            // 2. If attr1 and node1 are non-null, and node2 is node1, then:
            if ($attr1 && $node1 && $node2 === $node1) {
                // 1. For each attr in node2’s attribute list:
                foreach ($node2->attributes as $attr) {
                    // 1. If attr equals attr1, then return the result of adding DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC and DOCUMENT_POSITION_PRECEDING.
                    if ($attr === $attr1) {
                       return NodeInterface::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC + NodeInterface::DOCUMENT_POSITION_PRECEDING;
                    }
                    // 2. If attr equals attr2, then return the result of adding DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC and DOCUMENT_POSITION_FOLLOWING.
                    if ($attr === $attr2) {
                        return NodeInterface::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC + NodeInterface::DOCUMENT_POSITION_FOLLOWING;
                    }
                }
            }
        }
        // 6. If node1 or node2 is null, or node1’s root is not node2’s root,
        // then return the result of adding DOCUMENT_POSITION_DISCONNECTED, DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC,
        // and either DOCUMENT_POSITION_PRECEDING or DOCUMENT_POSITION_FOLLOWING,
        // with the constraint that this is to be consistent, together.
        if (!$node1 || !$node2 || DomIdioms::getRoot($node1) !== DomIdioms::getRoot($node2)) {
            return (
                NodeInterface::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC
                + NodeInterface::DOCUMENT_POSITION_DISCONNECTED
                + NodeInterface::DOCUMENT_POSITION_PRECEDING
            );
        }
        // 7. If node1 is an ancestor of node2 and attr1 is null, or node1 is node2 and attr2 is non-null,
        // then return the result of adding DOCUMENT_POSITION_CONTAINS to DOCUMENT_POSITION_PRECEDING.
        if (
            (!$attr1 && DomIdioms::isInclusiveDescendant($node1, $node2))
            || ($attr2 && $node1 === $node2)
        ) {
            return NodeInterface::DOCUMENT_POSITION_CONTAINS + NodeInterface::DOCUMENT_POSITION_PRECEDING;
        }
        // 8. If node1 is a descendant of node2 and attr2 is null, or node1 is node2 and attr1 is non-null,
        // then return the result of adding DOCUMENT_POSITION_CONTAINED_BY to DOCUMENT_POSITION_FOLLOWING.
        if (
            (!$attr2 && DomIdioms::isInclusiveDescendant($node2, $node1))
            || ($attr1 && $node1 === $node2)
        ) {
            return NodeInterface::DOCUMENT_POSITION_CONTAINED_BY + NodeInterface::DOCUMENT_POSITION_FOLLOWING;
        }
        // 9. If node1 is preceding node2, then return DOCUMENT_POSITION_PRECEDING.
        if (DomIdioms::isPrecedingSibling($node2, $node1)) {
            return NodeInterface::DOCUMENT_POSITION_PRECEDING;
        }
        // 10. Return DOCUMENT_POSITION_FOLLOWING.
        return NodeInterface::DOCUMENT_POSITION_FOLLOWING;
    }

    public function contains(?DOMNode $other): bool
    {
        return DomIdioms::isInclusiveDescendant($this, $other);
    }

    public function getParentElement(): ?Element
    {
        $parent = $this->parentNode;
        if (!$parent || $parent->nodeType !== XML_ELEMENT_NODE) {
            return null;
        }

        return $parent;
    }

    public function getRootNode(): DOMNode
    {
        /** @var DOMNode $this */
        $node = $this;
        while ($node->parentNode) {
            $node = $node->parentNode;
        }

        return $node;
    }

    public function isEqualNode(?DOMNode $otherNode): bool
    {
        return DomIdioms::isEqualNode($this, $otherNode);
    }
}
