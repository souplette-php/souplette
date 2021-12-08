<?php declare(strict_types=1);

namespace Souplette\Dom;

use Souplette\Dom\Api\NonElementParentNodeInterface;
use Souplette\Dom\Traits\NonElementParentNodeTrait;

final class DocumentFragment extends ParentNode implements NonElementParentNodeInterface
{
    use NonElementParentNodeTrait;

    public readonly int $nodeType;
    public readonly string $nodeName;

    public function __construct()
    {
        $this->nodeType = Node::DOCUMENT_FRAGMENT_NODE;
        $this->nodeName = '#document-fragment';
    }

    protected function clone(?Document $document, bool $deep = false): static
    {
        $copy = new self();
        $copy->_doc = $document ?? $this->_doc;
        if ($deep) {
            for ($child = $this->_first; $child; $child = $this->_next) {
                $childCopy = $child->clone($copy->_doc, true);
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
