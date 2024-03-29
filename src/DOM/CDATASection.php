<?php declare(strict_types=1);

namespace Souplette\DOM;

final class CDATASection extends Text
{
    public readonly int $nodeType;
    public readonly string $nodeName;

    /**
     * @internal
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(string $data = '')
    {
        $this->nodeType = Node::CDATA_SECTION_NODE;
        $this->nodeName = '#cdata-section';
        $this->_value = $data;
        $this->length = mb_strlen($data, 'utf-8');
    }
}
