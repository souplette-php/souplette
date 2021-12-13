<?php declare(strict_types=1);

namespace Souplette\Dom\Serialization;

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
}
