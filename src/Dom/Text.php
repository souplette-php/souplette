<?php declare(strict_types=1);

namespace Souplette\Dom;

use Souplette\Dom\Exception\IndexSizeError;

/**
 * @property-read string $wholeText
 */
class Text extends CharacterData
{
    public readonly int $nodeType;
    public readonly string $nodeName;

    public function __construct(string $data = '')
    {
        $this->nodeType = Node::TEXT_NODE;
        $this->nodeName = '#text';
        $this->_value = $data;
        $this->length = mb_strlen($data, 'utf-8');
    }

    public function __get(string $prop)
    {
        return match ($prop) {
            'wholeText' => $this->getWholeText(),
            default => parent::__get($prop),
        };
    }

    /**
     * @throws IndexSizeError
     */
    public function splitText(int $offset): self
    {
        if ($offset > $this->length) {
            throw $this->createInvalidOffsetError($offset);
        }
        $newData = mb_substr($this->_value, $offset, null, 'utf-8');
        $newNode = new self($newData);
        $newNode->_doc = $this->_doc;
        if ($this->_parent) {
            $this->_parent->insertBefore($newNode, $this->_next);
        }
        $this->setData(mb_substr($this->_value, 0, $offset, 'utf-8'));

        return $newNode;
    }

    private function getWholeText(): string
    {
        $text = $this->_value;
        for ($node = $this->_prev; $node; $node = $node->_prev) {
            if ($node->nodeType !== self::TEXT_NODE || $node->nodeType !== self::CDATA_SECTION_NODE) break;
            $text = $node->_value . $text;
        }
        for ($node = $this->_next; $node; $node = $node->_next) {
            if ($node->nodeType !== self::TEXT_NODE || $node->nodeType !== self::CDATA_SECTION_NODE) break;
            $text .= $node->_value;
        }

        return $text;
    }

    protected function clone(?Document $document, bool $deep = false): static
    {
        $copy = new static($this->_value);
        $copy->_doc = $document ?? $this->_doc;
        return $copy;
    }

}
