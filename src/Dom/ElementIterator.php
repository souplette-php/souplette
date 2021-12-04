<?php declare(strict_types=1);

namespace Souplette\Dom;

use DOMElement;
use DOMParentNode;
use Souplette\Dom\Legacy\Element;

final class ElementIterator
{
    /**
     * @return iterable<Element>
     */
    public static function descendants(DOMParentNode $root): iterable
    {
        $node = $root->firstElementChild;
        while ($node) {
            yield $node;
            if ($node->firstElementChild) {
                $node = $node->firstElementChild;
                continue;
            }
            while ($node) {
                if ($node === $root) {
                    break 2;
                }
                if ($node->nextElementSibling) {
                    $node = $node->nextElementSibling;
                    continue 2;
                }
                $node = $node->parentNode;
            }
        }
    }

    /**
     * @return iterable<Element>
     */
    public static function ancestors(DOMParentNode $root): iterable
    {
        $node = $root;
        while (($node = $node->parentNode) && $node->nodeType === XML_ELEMENT_NODE) {
            yield $node;
        }
    }

    /**
     * @return iterable<Element>
     */
    public static function following(DOMElement $node): iterable
    {
        while ($node = $node->nextElementSibling) {
            yield $node;
        }
    }

    /**
     * @return iterable<Element>
     */
    public static function preceding(DOMElement $node): iterable
    {
        while ($node = $node->previousElementSibling) {
            yield $node;
        }
    }
}
