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
}
