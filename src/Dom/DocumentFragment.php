<?php declare(strict_types=1);

namespace Souplette\Dom;

use Souplette\Dom\Api\NonElementParentNodeInterface;

final class DocumentFragment extends ParentNode implements NonElementParentNodeInterface
{
    public readonly int $nodeType;
    public readonly string $nodeName;

    public function __construct()
    {
        $this->nodeType = Node::DOCUMENT_FRAGMENT_NODE;
        $this->nodeName = '#document-fragment';
    }

    public function getElementById(string $elementId): ?Element
    {
        // TODO: Implement getElementById() method.
        return null;
    }

    public function cloneNode(bool $deep = false): static
    {
        $copy = new self();
        $copy->document = $this->document;
        if ($deep) {
            for ($child = $this->first; $child; $child = $this->next) {
                $childCopy = $child->cloneNode(true);
                $copy->adopt($childCopy);
                $copy->uncheckedAppendChild($childCopy);
            }
        }
        return $copy;
    }
    /**
     * @see https://dom.spec.whatwg.org/#dom-node-lookupprefix
     */
    public function lookupPrefix(?string $namespace): ?string
    {
        return null;
    }

    protected function locateNamespace(?string $prefix): ?string
    {
        return null;
    }
}
