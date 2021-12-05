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
        $this->value = $data;
        $this->length = mb_strlen($data, 'utf-8');
    }

    public function __get(string $prop)
    {
        return match ($prop) {
            'wholeText' => $this->getWholeText(),
            default => parent::__get($prop),
        };
    }

    public function splitText(int $offset): self
    {
        if ($offset > $this->length) {
            throw new IndexSizeError();
        }
        $newData = mb_substr($this->value, $offset, null, 'utf-8');
        $newNode = new self($newData);
        $newNode->document = $this->document;
        if ($this->parent) {
            if ($this->next) {
                $this->parent->uncheckedInsertBefore($newNode, $this->next);
            } else {
                $this->parent->uncheckedAppendChild($newNode);
            }
        }
        $this->setData(mb_substr($this->value, 0, $offset, 'utf-8'));

        return $newNode;
    }

    private function getWholeText(): string
    {
        $text = $this->value;
        for ($node = $this->prev; $node; $node = $node->prev) {
            if ($node->nodeType !== self::TEXT_NODE || $node->nodeType !== self::CDATA_SECTION_NODE) break;
            $text = $node->value . $text;
        }
        for ($node = $this->next; $node; $node = $node->next) {
            if ($node->nodeType !== self::TEXT_NODE || $node->nodeType !== self::CDATA_SECTION_NODE) break;
            $text .= $node->value;
        }

        return $text;
    }

    public function cloneNode(bool $deep = false): static
    {
        $copy = new static($this->value);
        $copy->document = $this->document;
        return $copy;
    }
}
