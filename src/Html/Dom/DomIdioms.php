<?php declare(strict_types=1);

namespace Souplette\Html\Dom;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use JetBrains\PhpStorm\Pure;

final class DomIdioms
{
    public static function getOwnerDocument(DOMNode $node): ?DOMDocument
    {
        $document = $node->ownerDocument;
        if (!$document && ($node->nodeType === XML_HTML_DOCUMENT_NODE ||$node->nodeType === XML_DOCUMENT_NODE)) {
            /** @var DOMDocument $node */
            return $node;
        }

        return $document;
    }

    public static function getRoot(DOMNode $node): DOMNode
    {
        while ($node->parentNode) {
            $node = $node->parentNode;
        }
        return $node;
    }

    public static function isInclusiveDescendant(DOMNode $parent, ?DOMNode $other = null): bool
    {
        if (!$other) return false;
        for ($node = $other; $node; $node = $node->parentNode) {
            if ($node === $parent) return true;
        }
        return false;
    }

    public static function isPrecedingSibling(DOMNode $reference, ?DOMNode $other = null): bool
    {
        if (!$other) return false;
        for ($node = $reference->previousSibling; $node; $node = $node->previousSibling) {
            if ($node === $other) return true;
        }
        return false;
    }

    public static function getElementsByClassName(DOMDocument $doc, string $classNames, DOMNode $context): DOMNodeList
    {
        // TODO: match must be case-insensitive in quirks mode
        $classes = preg_split('/\s+/', $classNames, -1, PREG_SPLIT_NO_EMPTY);
        $exprs = [];
        foreach ($classes as $class) {
            $exprs[] = sprintf("contains(concat(' ', normalize-space(@class), ' '), ' %s ')", $class);
        }
        $expr = sprintf('descendant-or-self::*[@class and %s]', implode(' and ', $exprs));

        return (new DOMXPath($doc))->query($expr, $context);
    }

    /**
     * @param DOMDocument $doc
     * @param array<DOMNode|string> $nodes
     * @return DOMNode
     */
    public static function convertNodesIntoNode(DOMDocument $doc, array $nodes): DOMNode
    {
        if (count($nodes) === 1) {
            $node = $nodes[0];
            if (is_string($node)) {
                $node = $doc->createTextNode($node);
            }
            return $doc->importNode($node, true);
        }

        $frag = $doc->createDocumentFragment();
        foreach ($nodes as $node) {
            if (is_string($node)) {
                $node = $doc->createTextNode($node);
            }
            $frag->appendChild($doc->importNode($node, true));
        }

        return $frag;
    }

    #[Pure]
    public static function findViablePreviousSibling(DOMNode $refNode, array $nodes): ?DOMNode
    {
        for ($sibling = $refNode->previousSibling; $sibling; $sibling = $sibling->previousSibling) {
            if (!in_array($sibling, $nodes, true)) {
                return $sibling;
            }
        }
        return null;
    }

    #[Pure]
    public static function findViableNextSibling(DOMNode $refNode, array $nodes): ?DOMNode
    {
        for ($sibling = $refNode->nextSibling; $sibling; $sibling = $sibling->nextSibling) {
            if (!in_array($sibling, $nodes, true)) {
                return $sibling;
            }
        }
        return null;
    }

    /**
     * @see https://dom.spec.whatwg.org/#concept-node-replace-all
     *
     * @param DOMNode $node
     * @param DOMNode $parent
     */
    public static function replaceAllWithNodeWithinParent(DOMNode $node, DOMNode $parent): void
    {
        // most of the spec steps are related to mutation records which we do not support.
        while ($parent->firstChild) {
            $parent->removeChild($parent->firstChild);
        }
        $parent->appendChild($node);
    }
}
