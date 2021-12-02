<?php declare(strict_types=1);

namespace Souplette\Dom\Node;

use Souplette\Dom\Node\Traits\ParentNodeTrait;

final class DocumentFragment extends Node
{
    use ParentNodeTrait;

    public readonly int $nodeType;
    public readonly string $nodeName;

    public function __construct()
    {
        $this->nodeType = Node::DOCUMENT_FRAGMENT_NODE;
        $this->nodeName = '#document-fragment';
    }

    public function __get(string $prop)
    {
        return match ($prop) {
            'nodeValue' => null,
            'textContent' => $this->getTextContent(),
            'children' => $this->getChildren(),
            'firstElementChild' => $this->getFirstElementChild(),
            'lastElementChild' => $this->getLastElementChild(),
            'childElementCount' => $this->getChildElementCount(),
            default => parent::__get($prop),
        };
    }

    public function __set(string $prop, mixed $value)
    {
        match ($prop) {
            'textContent' => $this->setTextContent($value),
            default => parent::__set($prop, $value),
        };
    }
}
