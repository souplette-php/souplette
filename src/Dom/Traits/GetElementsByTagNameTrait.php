<?php declare(strict_types=1);

namespace Souplette\Dom\Traits;

use Souplette\Dom\Element;
use Souplette\Dom\Traversal\ElementTraversal;

trait GetElementsByTagNameTrait
{
    /**
     * @see https://dom.spec.whatwg.org/#concept-getelementsbytagname
     * @return Element[]
     */
    public function getElementsByTagName(string $qualifiedName): array
    {
        $collection = [];
        if ($qualifiedName === '*') {
            foreach (ElementTraversal::descendantsOf($this) as $element) {
                $collection[] = $element;
            }
            return $collection;
        }
        if ($this->isHTML) {
            $lowerName = strtolower($qualifiedName);
            foreach (ElementTraversal::descendantsOf($this) as $element) {
                if (
                    ($element->isHTML && $element->qualifiedName === $lowerName)
                    || (!$element->isHTML && $element->qualifiedName === $qualifiedName)
                ) {
                    $collection[] = $element;
                }
            }
            return $collection;
        }
        foreach (ElementTraversal::descendantsOf($this) as $element) {
            if ($element->tagName === $qualifiedName) $collection[] = $element;
        }
        return $collection;
    }

    /**
     * @see https://dom.spec.whatwg.org/#concept-getelementsbytagnamens
     * @return Element[]
     */
    public function getElementsByTagNameNS(?string $namespace, string $localName): array
    {
        $namespace = $namespace ?: null;
        $collection = [];
        if ($namespace === '*' && $localName === '*') {
            foreach (ElementTraversal::descendantsOf($this) as $element) {
                $collection[] = $element;
            }
            return $collection;
        }
        if ($namespace === '*') {
            foreach (ElementTraversal::descendantsOf($this) as $element) {
                if ($element->localName === $localName) $collection[] = $element;
            }
            return $collection;
        }
        if ($localName === '*') {
            foreach (ElementTraversal::descendantsOf($this) as $element) {
                if ($element->namespaceURI === $namespace) $collection[] = $element;
            }
            return $collection;
        }
        foreach (ElementTraversal::descendantsOf($this) as $element) {
            if ($element->localName === $localName && $element->namespaceURI === $namespace) {
                $collection[] = $element;
            }
        }
        return $collection;
    }
}
