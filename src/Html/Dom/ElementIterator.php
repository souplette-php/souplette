<?php declare(strict_types=1);

namespace Souplette\Html\Dom;

final class ElementIterator
{
    /**
     * @return \Generator & iterable<\DOMElement>
     */
    public static function descendants(\DOMParentNode $root): \Generator
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

    public static function ancestors(\DOMParentNode $root): \Generator
    {
        $node = $root;
        while (($node = $node->parentNode) && $node->nodeType === XML_ELEMENT_NODE) {
            yield $node;
        }
    }

    public static function following(\DOMElement $node): \Generator
    {
        while ($node = $node->nextElementSibling) {
            yield $node;
        }
    }

    public static function preceding(\DOMElement $node): \Generator
    {
        while ($node = $node->previousElementSibling) {
            yield $node;
        }
    }
}
