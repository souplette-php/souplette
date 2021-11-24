<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query;

final class TypeMatcher
{
    public static function isOfType(\DOMElement $element, string $type, bool $caseInsensitive = true): bool
    {
        return match($caseInsensitive) {
            true => strcasecmp($element->localName, $type) === 0,
            false => $element->localName === $type,
        };
    }

    public static function isSameType(\DOMElement $element, \DOMElement $other, bool $caseInsensitive = true): bool
    {
        return match($caseInsensitive) {
            true => strcasecmp($element->localName, $other->localName) === 0,
            false => $element->localName === $other->localName,
        };
    }
}
