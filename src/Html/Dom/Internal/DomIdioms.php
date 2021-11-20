<?php declare(strict_types=1);

namespace Souplette\Html\Dom\Internal;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNode;
use JetBrains\PhpStorm\Pure;
use Souplette\Css\Selectors\SelectorQuery;
use Souplette\Html\Dom\Node\HtmlDocument;
use Souplette\Html\Namespaces;

final class DomIdioms
{
    const ASCII_WHITESPACE = " \n\t\r\f";

    public static function getOwnerDocument(DOMNode $node): ?DOMDocument
    {
        $document = $node->ownerDocument;
        if (!$document && ($node->nodeType === XML_HTML_DOCUMENT_NODE || $node->nodeType === XML_DOCUMENT_NODE)) {
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

    public static function getElementsByClassName(\DOMParentNode $element, string $classNames): array
    {
        $selectorText = '';
        foreach (self::splitInputOnAsciiWhitespace($classNames) as $class) {
            $selectorText .= ".${$class}";
        }

        return SelectorQuery::queryAll($element, $selectorText);
    }

    /**
     * @param DOMDocument $doc
     * @param array<DOMNode|string> $nodes
     * @return DOMNode
     */
    public static function convertNodesIntoNode(DOMDocument $doc, array $nodes): DOMNode
    {
        if (\count($nodes) === 1) {
            $node = $nodes[0];
            if (\is_string($node)) {
                $node = $doc->createTextNode($node);
            }
            return $doc->importNode($node, true);
        }

        $frag = $doc->createDocumentFragment();
        foreach ($nodes as $node) {
            if (\is_string($node)) {
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
            if (!\in_array($sibling, $nodes, true)) {
                return $sibling;
            }
        }
        return null;
    }

    #[Pure]
    public static function findViableNextSibling(DOMNode $refNode, array $nodes): ?DOMNode
    {
        for ($sibling = $refNode->nextSibling; $sibling; $sibling = $sibling->nextSibling) {
            if (!\in_array($sibling, $nodes, true)) {
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

    /**
     * @see https://dom.spec.whatwg.org/#concept-element-attributes-get-by-name
     */
    public static function getAttributeByName(DOMElement $node, string $qualifiedName): ?DOMAttr
    {
        // 1. If element is in the HTML namespace and its node document is an HTML document,
        // then set qualifiedName to qualifiedName in ASCII lowercase.
        if ($node->namespaceURI === Namespaces::HTML && $node->ownerDocument->nodeType === XML_HTML_DOCUMENT_NODE) {
            $qualifiedName = strtolower($qualifiedName);
        }
        // 2. Return the first attribute in element’s attribute list whose qualified name is qualifiedName;
        // otherwise null.
        foreach ($node->attributes as $attribute) {
            if ($attribute->nodeName === $qualifiedName) {
                return $attribute;
            }
        }
        return null;
    }

    public static function splitInputOnAsciiWhitespace(string $input): iterable
    {
        $token = strtok($input, self::ASCII_WHITESPACE);
        $i = 0;
        while ($token) {
            yield $i++ => $token;
            $token = strtok(self::ASCII_WHITESPACE);
        }
    }
}
