<?php declare(strict_types=1);

namespace Souplette\DOM\Traits;

use Souplette\DOM\Element;
use Souplette\DOM\Traversal\ElementTraversal;

/**
 * Used by Document & Element
 */
trait GetElementsByTagNameTrait
{
    /**
     * @see https://dom.spec.whatwg.org/#concept-getelementsbytagname
     * @return Element[]
     */
    public function getElementsByTagName(string $qualifiedName): array
    {
        if ($qualifiedName === '*') {
            return iterator_to_array(ElementTraversal::descendantsOf($this));
        }
        if ($this->isHTML) {
            $lowerName = strtolower($qualifiedName);
            return iterator_to_array(ElementTraversal::descendantsOf($this, fn($el) => (
                ($el->isHTML && $el->qualifiedName === $lowerName)
                || (!$el->isHTML && $el->qualifiedName === $qualifiedName)
            )));
        }
        return iterator_to_array(ElementTraversal::descendantsOf($this, fn($el) => (
            $el->tagName === $qualifiedName
        )));
    }

    /**
     * @see https://dom.spec.whatwg.org/#concept-getelementsbytagnamens
     * @return Element[]
     */
    public function getElementsByTagNameNS(?string $namespace, string $localName): array
    {
        $namespace = $namespace ?: null;
        if ($namespace === '*' && $localName === '*') {
            return iterator_to_array(ElementTraversal::descendantsOf($this));
        }
        if ($namespace === '*') {
            return iterator_to_array(ElementTraversal::descendantsOf($this, fn($el) => (
                $el->localName === $localName
            )));
        }
        if ($localName === '*') {
            return iterator_to_array(ElementTraversal::descendantsOf($this, fn($el) => (
                $el->namespaceURI === $namespace
            )));
        }
        return iterator_to_array(ElementTraversal::descendantsOf($this, fn($el) => (
            $el->localName === $localName && $el->namespaceURI === $namespace
        )));
    }
}
