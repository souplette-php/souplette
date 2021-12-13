<?php declare(strict_types=1);

namespace Souplette\Xml;

use Souplette\Dom\Exception\InvalidCharacterError;
use Souplette\Dom\Exception\NamespaceError;
use Souplette\Dom\Namespaces;

/**
 * @internal
 */
final class QName
{
    const QNAME_PATTERN = <<<'REGEXP'
    /
    (?(DEFINE)
        (?<NCNameStartChar> [A-Z_a-z]
            | [\x{C0}-\x{D6}] | [\x{D8}-\x{F6}] | [\x{F8}-\x{2FF}]
            | [\x{370}-\x{37D}] | [\x{37F}-\x{1FFF}] | [\x{200C}-\x{200D}] | [\x{2070}-\x{218F}] | [\x{2C00}-\x{2FEF}]
            | [\x{3001}-\x{D7FF}] | [\x{F900}-\x{FDCF}] | [\x{FDF0}-\x{FFFD}] | [\x{10000}-\x{EFFFF}]
        )
        (?<NCNameChar> (?&NCNameStartChar) | - | \. | [0-9] | \x{B7} | [\x{0300}-\x{036F}] | [\x{203F}-\x{2040}] )
        (?<NCName> (?&NCNameStartChar) (?&NCNameChar)* )
    )
        ^
        (?: (?<prefix> (?&NCName)) :)?
        (?<localName> (?&NCName) )
        $
    /Sux
    REGEXP;

    /**
     * @see https://www.w3.org/TR/xml/#NT-Name
     */
    const NAME_PATTERN = <<<'REGEXP'
    /
    (?(DEFINE)
        (?<NameStartChar> : | [A-Z] | _ | [a-z] | [\x{C0}-\x{D6}] | [\x{D8}-\x{F6}] | [\x{F8}-\x{2FF}]
            | [\x{370}-\x{37D}] | [\x{37F}-\x{1FFF}] | [\x{200C}-\x{200D}] | [\x{2070}-\x{218F}] | [\x{2C00}-\x{2FEF}]
            | [\x{3001}-\x{D7FF}] | [\x{F900}-\x{FDCF}] | [\x{FDF0}-\x{FFFD}] | [\x{10000}-\x{EFFFF}]
        )
        (?<NameChar> (?&NameStartChar) | - | \. | [0-9] | \x{B7} | [\x{0300}-\x{036F}] | [\x{203F}-\x{2040}] )
    )
        ^ (?&NameStartChar) (?&NameChar)* $
    /Sux
    REGEXP;

    public static function isValidName(string $name): bool
    {
        return (bool)preg_match(self::NAME_PATTERN, $name);
    }

    /**
     * @throws NamespaceError|InvalidCharacterError
     */
    public static function validateAndExtract(string $qualifiedName, ?string $namespace = null): array
    {
        // 1. If namespace is the empty string, then set it to null.
        $namespace = $namespace ?: null;
        // 2. Validate qualifiedName.
        // To validate a qualifiedName,
        // throw an "InvalidCharacterError" DOMException if qualifiedName does not match the QName production.
        if (!preg_match(self::QNAME_PATTERN, $qualifiedName, $matches, \PREG_UNMATCHED_AS_NULL)) {
            throw new InvalidCharacterError(sprintf(
                'Provided qualified name "%s" is not a valid name.',
                $qualifiedName,
            ));
        }
        // 3. Let prefix be null.
        // 4. Let localName be qualifiedName.
        // 5. If qualifiedName contains a U+003A (:),
        // then strictly split the string on it and set prefix to the part before and localName to the part after.
        $prefix = $matches['prefix'] ?? null;
        $localName = $matches['localName'];
        // 6. If prefix is non-null and namespace is null, then throw a "NamespaceError" DOMException.
        if ($prefix && !$namespace) {
            throw new NamespaceError('Prefix given with no namespace.');
        }
        // 7. If prefix is "xml" and namespace is not the XML namespace, then throw a "NamespaceError" DOMException.
        if ($prefix === 'xml' && $namespace !== Namespaces::XML) {
            throw new NamespaceError('The "xml" prefix must be in the XML namespace.');
        }
        // 8. If either qualifiedName or prefix is "xmlns" and namespace is not the XMLNS namespace, then throw a "NamespaceError" DOMException.
        if (
            ($prefix === 'xmlns' || $qualifiedName === 'xmlns')
            && $namespace !== Namespaces::XMLNS
        ) {
            throw new NamespaceError('xmlns prefix or QName must be in the XMLNS namespace.');
        }
        // 9. If namespace is the XMLNS namespace and neither qualifiedName nor prefix is "xmlns", then throw a "NamespaceError" DOMException.
        //if ($namespace === Namespaces::XMLNS)
        // 10. Return namespace, prefix, and localName.
        return [$namespace, $prefix, $localName];
    }
}
