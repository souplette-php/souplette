<?php declare(strict_types=1);

namespace Souplette\Tests;

use Souplette\Dom\Element;
use Souplette\Dom\Node;

final class Utils
{
    public static function cartesianProduct(array $set): iterable
    {
        if (!$set) return;

        $iterator = function(array $set) use (&$iterator) {
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

    public static function elementPath(Element $element): string
    {
        $path = '';
        $node = $element;
        while ($node && $node->nodeType === Node::ELEMENT_NODE) {
            $name = $node->qualifiedName;
            $index = 1;
            $showIndex = false;
            $sibling = $node;
            while ($sibling = $sibling->previousElementSibling) {
                if ($sibling->qualifiedName === $name) $index++;
            }
            if ($index === 1) {
                $sibling = $node;
                while ($sibling = $sibling->nextElementSibling) {
                    if ($sibling->qualifiedName === $name) {
                        $showIndex = true;
                        break;
                    }
                }
            } else {
                $showIndex = true;
            }
            $path = $showIndex ? "/{$name}[{$index}]{$path}" : "/{$name}{$path}";
            $node = $node->parentNode;
        }
        return $path;
    }
}
