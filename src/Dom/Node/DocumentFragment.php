<?php declare(strict_types=1);

namespace Souplette\Dom\Node;

final class DocumentFragment extends ParentNode
{
    public readonly int $nodeType;
    public readonly string $nodeName;

    public function __construct()
    {
        $this->nodeType = Node::DOCUMENT_FRAGMENT_NODE;
        $this->nodeName = '#document-fragment';
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
}
