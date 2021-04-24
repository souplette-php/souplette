<?php declare(strict_types=1);

namespace Souplette\Tests;

final class Utils
{
    public static function cartesianProduct(array $set): \Generator
    {
        if (!$set) return;

        $iterator = function(array $set) use(&$iterator) {
            if (!$set) {
                yield [];
                return;
            }

            $last = array_key_last($set);
            $subset = array_pop($set);
            foreach ($iterator($set) as $product) {
                foreach ($subset as $value) {
                    yield $product + [$last => $value];
                }
            }
        };

        yield from $iterator($set);
    }

    public static function elementPath(\DOMElement $element): string
    {
        $path = '';
        $node = $element;
        while ($node && $node->nodeType === XML_ELEMENT_NODE) {
            $name = $node->localName;
            $index = 0;
            $sibling = $node;
            while ($sibling = $sibling->previousElementSibling) {
                $index++;
            }
            $path = "/{$name}[{$index}]" . $path;
            $node = $node->parentNode;
        }
        return $path;
    }
}
