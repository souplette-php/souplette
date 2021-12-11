<?php declare(strict_types=1);

namespace Souplette\Xml\Serializer;

/**
 * @see https://w3c.github.io/DOM-Parsing/#the-namespace-prefix-map
 */
final class NamespacePrefixMap
{
    private array $map = [];

    /**
     * @see https://w3c.github.io/DOM-Parsing/#dfn-add
     */
    public function add(string $prefix, string $namespace): void
    {
        $this->map[$namespace][] = $prefix;
    }

    /**
     * @see https://w3c.github.io/DOM-Parsing/#dfn-found
     */
    public function has(string $prefix, string $namespace): bool
    {
        if ($candidates = $this->map[$namespace] ?? null) {
            return \in_array($prefix, $candidates);
        }
        return false;
    }

    /**
     * @see https://w3c.github.io/DOM-Parsing/#dfn-copy-a-namespace-prefix-map
     */
    public function copy(): self
    {
        $map = new self();
        $map->map = $this->map;
        return $map;
    }

    /**
     * @see https://w3c.github.io/DOM-Parsing/#dfn-retrieving-a-preferred-prefix-string
     */
    public function retrievePreferredPrefix(?string $preferredPrefix, string $namespace): ?string
    {
        $candidates = $this->map[$namespace] ?? null;
        if (!$candidates) return null;

        foreach ($candidates as $prefix) {
            if ($prefix === $preferredPrefix) return $prefix;
        }

        return end($candidates);
    }
}
