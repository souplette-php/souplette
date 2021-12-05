<?php declare(strict_types=1);

namespace Souplette\Dom\Node;

final class CDATASection extends Text
{
    public readonly int $nodeType;
    public readonly string $nodeName;

    /**
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(string $data = '')
    {
        $this->nodeType = Node::CDATA_SECTION_NODE;
        $this->nodeName = '#cdata-section';
        $this->value = $data;
        $this->length = mb_strlen($data, 'utf-8');
    }
}