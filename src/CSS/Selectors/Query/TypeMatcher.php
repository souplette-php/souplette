<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Query;

use Souplette\DOM\Element;

final class TypeMatcher
{
    public static function isOfType(Element $element, string $type, bool $caseInsensitive = true): bool
    {
        return match ($caseInsensitive) {
            true => strcasecmp($element->localName, $type) === 0,
            false => $element->localName === $type,
        };
    }
}
