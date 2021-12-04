<?php declare(strict_types=1);

namespace Souplette\Dom\Node;

use Souplette\Dom\Namespaces;
use Souplette\Dom\Node\Traits\ChildNodeTrait;
use Souplette\Dom\Node\Traits\NonDocumentTypeChildNodeTrait;

/**
 * @property string $id
 * @property string $className
 */
final class Element extends ParentNode
{
    use ChildNodeTrait;
    use NonDocumentTypeChildNodeTrait;

    public readonly int $nodeType;
    public readonly string $nodeName;
    public readonly string $localName;
    public readonly string $tagName;
    public readonly ?string $namespaceURI;
    public readonly ?string $prefix;
    public readonly bool $isHTML;

    public function __construct(string $localName, ?string $namespace = null, ?string $prefix = null)
    {
        $this->nodeType = Node::ELEMENT_NODE;
        $this->localName = $localName;
        $this->namespaceURI = $namespace;
        $this->prefix = $prefix;
        $this->isHTML = $namespace === Namespaces::HTML;
        if ($this->isHTML) {
            $this->tagName = strtoupper($localName);
        } else {
            $this->tagName = $prefix ? "{$prefix}:{$localName}" : $localName;
        }
        $this->nodeName = $this->tagName;
    }

    public function __get(string $prop)
    {
        return match ($prop) {
            'nextElementSibling' => $this->getNextElementSibling(),
            'previousElementSibling' => $this->getPreviousElementSibling(),
            default => parent::__get($prop),
        };
    }

    public function isEqualNode(?Node $otherNode): bool
    {
        if (!$otherNode) return false;
        if ($otherNode === $this) return true;
        if ($otherNode->nodeType !== $this->nodeType) return false;
        foreach ($this->attributes as $attribute) {
            $otherAttr = $otherNode->attributes->getNamedItem($attribute->name);
            if (!$attribute->isEqualNode($otherAttr)) {
                return false;
            }
        }
        return $this->areChildrenEqual($otherNode);
    }
}
