<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Helper;

final class NthChildMatchHelper
{
    public static function matchesFirstChild(\DOMElement $element): bool
    {
        return $element->previousElementSibling === null;
    }

    public static function matchesLastChild(\DOMElement $element): bool
    {
        return $element->nextElementSibling === null;
    }

    public static function matchesNthChild(
        \DOMElement $element,
        int $a,
        int $b,
        bool $sameType = false,
        bool $fromEnd = false
    ): bool {
        $index = self::nthChildIndex($element, $sameType, $fromEnd);
        if ($a === 0) {
            return $index === $b;
        }
        if ($a > 0) {
            if ($index < $b) return false;
            return ($index - $b) % $a === 0;
        }
        if ($index > $b) return false;
        return ($b - $index) % -($a) === 0;
    }

    private static function nthChildIndex(\DOMElement $element, bool $sameType = false, bool $fromEnd = false): int
    {
        $index = 1;
        foreach (self::siblings($element, !$fromEnd) as $sibling) {
            if (!$sameType || TypeMatchHelper::isSameType($element, $sibling)) {
                $index += 1;
            }
        }

        return $index;
    }

    /**
     * @param \DOMElement $element
     * @param bool $reverse
     * @return \Generator|iterable<\DOMElement>
     */
    private static function siblings(\DOMElement $element, bool $reverse = false): \Generator
    {
        $current = $reverse ? $element->previousElementSibling : $element->nextElementSibling;
        while ($current) {
            yield $current;
            $current = $reverse ? $current->previousElementSibling : $current->nextElementSibling;
        }
    }
}
