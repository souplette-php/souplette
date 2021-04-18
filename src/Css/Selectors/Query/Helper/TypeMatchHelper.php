<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Helper;

final class TypeMatchHelper
{
    public static function isOfType(\DOMElement $element, string $type): bool
    {
        return strcasecmp($element->localName, $type) === 0;
    }

    public static function isSameType(\DOMElement $element, \DOMElement $other): bool
    {
        return strcasecmp($element->localName, $other->localName) === 0;
    }
}
