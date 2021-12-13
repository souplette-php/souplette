<?php declare(strict_types=1);

namespace Souplette\Xml\Serializer;

/**
 * @see https://w3c.github.io/DOM-Parsing/#the-namespace-prefix-map
 * @internal
 */
final class NamespacePrefixMap
{
    private array $nsToPrefixes = [];
    private array $prefixToNS = [];

    /**
     * @see https://w3c.github.io/DOM-Parsing/#dfn-add
     */
    public function add(string $prefix, string $namespace): void
    {
        // Before we add prefix and namespace to a map,
        // we need to remove the prefix associated to another namespace from the map.
        // see https://github.com/w3c/DOM-Parsing/issues/45
        if ($oldNs = $this->prefixToNS[$prefix] ?? null) {
            $index = array_search($prefix, $this->nsToPrefixes[$oldNs]);
            array_splice($this->nsToPrefixes[$oldNs], $index, 1);
        }

        $this->nsToPrefixes[$namespace][] = $prefix;
        $this->prefixToNS[$prefix] = $namespace;
    }

    /**
     * @see https://w3c.github.io/DOM-Parsing/#dfn-found
     */
    public function has(string $prefix, string $namespace): bool
    {
        if ($candidates = $this->nsToPrefixes[$namespace] ?? null) {
            return \in_array($prefix, $candidates);
        }
        return false;
    }

    public function lookupNamespace(string $prefix): ?string
    {
        return $this->prefixToNS[$prefix] ?? null;
    }

    /**
     * @see https://w3c.github.io/DOM-Parsing/#dfn-copy-a-namespace-prefix-map
     */
    public function copy(): self
    {
        $map = new self();
        $map->nsToPrefixes = $this->nsToPrefixes;
        $map->prefixToNS = $this->prefixToNS;
        return $map;
    }

    /**
     * @see https://w3c.github.io/DOM-Parsing/#dfn-retrieving-a-preferred-prefix-string
     */
    public function retrievePreferredPrefix(?string $preferredPrefix, ?string $namespace): ?string
    {
        $candidates = $this->nsToPrefixes[$namespace] ?? null;
        if (!$candidates) return null;

        foreach ($candidates as $prefix) {
            if ($prefix === $preferredPrefix) return $prefix;
        }

        return end($candidates);
    }
}
