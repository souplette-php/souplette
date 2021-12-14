<?php declare(strict_types=1);

namespace Souplette\Html\Custom;

final class CustomElement
{
    private const VALID_NAME = <<<'REGEXP'
    /(?(DEFINE)
        (?<PCENChar> [0-9a-z_.-]
            | [\x{B7}\x{C0}-\x{D6}\x{D8}-\x{F6}\x{F8}-\x{37D}\x{37F}-\x{1FFF}\x{200C}-\x{200D}\x{203F}-\x{2040}]
            | [\x{2070}-\x{218F}\x{2C00}-\x{2FEF}\x{3001}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFFD}\x{10000}-\x{EFFFF}]
        )
    )
        ^ [a-z] (?&PCENChar)* - (?&PCENChar)* $
    /Sux
    REGEXP;

    public static function isValidName(string $localName): bool
    {
        // This quickly rejects all common built-in element names.
        if (!$localName
            || strpos($localName, '-', 1) === false
            || !ctype_lower($localName[0])
        ) {
            return false;
        }
        return (bool)preg_match(self::VALID_NAME, $localName);
    }
}
