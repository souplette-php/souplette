<?php declare(strict_types=1);

namespace Souplette\Xml\Serializer;

use Souplette\Dom\Element;

final class NamespaceContext
{
    // https://w3c.github.io/DOM-Parsing/#dfn-context-namespace
    public ?string $contextNamespace = null;
    /**
     * Map a namespace URI to a list of prefixes.
     * @see https://w3c.github.io/DOM-Parsing/#the-namespace-prefix-map
     * @var array<string, string[]>
     */
    private array $nsToPrefixes = [];
    /** @var array<string, string>  */
    private array $prefixToNS = [];

    public function add(?string $prefix, ?string $namespace): void
    {
        // Before we add prefix and namespace to a map,
        // we need to remove the prefix associated to another namespace from the map.
        // see https://github.com/w3c/DOM-Parsing/issues/45
        //if ($oldNs = $this->prefixToNS[$prefix] ?? null) {
        //    $index = array_search($prefix, $this->nsToPrefixes[$oldNs]);
        //    array_splice($this->nsToPrefixes[$oldNs], $index, 1);
        //}
        $this->prefixToNS[$prefix] = $namespace;
        $this->nsToPrefixes[$namespace][] = $prefix ?: null;
    }

    public function recordNamespaceInformation(Element $element): ?string
    {
        $localDefaultNamespace = null;
        foreach ($element->_attrs as $attr) {
            if (!$attr->prefix && $attr->localName === 'xmlns') {
                $localDefaultNamespace = $attr->_value ?: null;
            } else if ($attr->prefix === 'xmlns') {
                $this->add($attr->localName, $attr->_value);
            }
        }
        return $localDefaultNamespace;
    }

    public function lookupNamespaceURI(?string $prefix): ?string
    {
        return $this->prefixToNS[$prefix] ?? null;
    }

    public function inheritLocalDefaultNamespace(?string $localDefaultNamespace)
    {
        if ($localDefaultNamespace === null) return;
        $this->contextNamespace = $localDefaultNamespace;
    }

    /**
     * @return string[]
     */
    public function candidatePrefixes(string $namespace): array
    {
        return $this->nsToPrefixes[$namespace] ?? [];
    }

    /**
     * https://w3c.github.io/DOM-Parsing/#dfn-retrieving-a-preferred-prefix-string
     */
    public function retrievePreferredPrefix(string $ns, ?string $preferredPrefix): ?string
    {
        $nsForPreferred = $this->prefixToNS[$preferredPrefix] ?? null;
        // Preserve the prefix if the prefix is used in the scope and the namespace
        // for it is matches to the node's one.
        // This is equivalent to the following step in the specification:
        // 2.1. If prefix matches preferred prefix, then stop running these steps and
        // return prefix.

        // weak comparison is intentional so that null equals ''
        if ($preferredPrefix && $nsForPreferred && $ns == $nsForPreferred) {
            return $preferredPrefix;
        }
        $candidates = $this->nsToPrefixes[$ns] ?? [];
        // Get the last effective prefix.
        //
        // <el1 xmlns:p="U1" xmlns:q="U1">
        //   <el2 xmlns:q="U2">
        //    el2.setAttributeNS(U1, 'n', 'v');
        // We should get 'p'.
        //
        // <el1 xmlns="U1">
        //  el1.setAttributeNS(U1, 'n', 'v');
        // We should not get '' for attributes.
        for ($i = \count($candidates) - 1; $i >= 0; $i--) {
            $candidate = $candidates[$i];
            $nsForCandidate = $this->prefixToNS[$candidate] ?? null;
            // weak comparison is intentional so that null equals ''
            if ($nsForCandidate == $ns) return $candidate;
        }
        // No prefixes for |ns|.
        // Preserve the prefix if the prefix is not used in the current scope.
        if ($preferredPrefix && !$nsForPreferred) return $preferredPrefix;
        // If a prefix is not specified, or the prefix is mapped to a
        // different namespace, we should generate new prefix.
        return null;
    }
}
