<?php declare(strict_types=1);

namespace Souplette\Dom;

use Souplette\Dom\Api\ChildNodeInterface;
use Souplette\Dom\Api\NonDocumentTypeChildNodeInterface;
use Souplette\Dom\Exception\IndexSizeError;
use Souplette\Dom\Traits\ChildNodeTrait;
use Souplette\Dom\Traits\NonDocumentTypeChildNodeTrait;

/**
 * @property string $data
 * @property-read int $length
 */
abstract class CharacterData extends Node implements ChildNodeInterface, NonDocumentTypeChildNodeInterface
{
    use ChildNodeTrait;
    use NonDocumentTypeChildNodeTrait;

    protected int $length = 0;

    public function __get(string $prop)
    {
        return match ($prop) {
            'data', 'nodeValue', 'textContent' => $this->value ?? '',
            'length' => $this->length,
            'nextElementSibling' => $this->getNextElementSibling(),
            'previousElementSibling' => $this->getPreviousElementSibling(),
            default => parent::__get($prop),
        };
    }

    public function __set(string $prop, mixed $value)
    {
        match ($prop) {
            'data', 'nodeValue', 'textContent' => $this->setData($value),
        };
    }

    public function getNodeValue(): ?string
    {
        return $this->value ?? '';
    }

    public function setNodeValue(string $value): void
    {
        $this->setData($value);
    }

    public function getTextContent(): ?string
    {
        return $this->value ?? '';
    }

    public function setTextContent(string $value): void
    {
        $this->setData($value);
    }

    public function isEqualNode(?Node $otherNode): bool
    {
        if (!$otherNode) return false;
        if ($otherNode === $this) return true;
        return $otherNode->nodeType === $this->nodeType
            && $this->value === $otherNode->value;
    }

    /**
     * https://dom.spec.whatwg.org/#dom-characterdata-substringdata
     * @throws IndexSizeError
     */
    public function substringData(int $offset, int $count): string
    {
        if ($offset > $this->length) {
            throw new IndexSizeError();
        }
        return mb_substr($this->value ?? '', $offset, $count, 'utf-8');
    }

    public function appendData(string $data): void
    {
        $this->value .= $data;
        $this->length += mb_strlen($data, 'utf-8');
    }

    /**
     * @throws IndexSizeError
     */
    public function insertData(int $offset, string $data): void
    {
        if ($offset > $this->length) {
            throw new IndexSizeError();
        }

        $head = mb_substr($this->value, 0, $offset, 'utf-8');
        $tail = mb_substr($this->value, $offset, null, 'utf-8');

        $this->value = $head . $data . $tail;
        $this->length += mb_strlen($data, 'utf-8');
    }

    /**
     * @throws IndexSizeError
     */
    public function deleteData(int $offset, int $count): void
    {
        if ($offset > $this->length) {
            throw new IndexSizeError();
        }
        if ($count === 0) return;
        $head = mb_substr($this->value, 0, $offset, 'utf-8');
        $tail = mb_substr($this->value, $offset + $count, null, 'utf-8');
        $this->setData($head . $tail);
    }

    /**
     * @throws IndexSizeError
     */
    public function replaceData(int $offset, int $count, string $data): void
    {
        if ($offset > $this->length) {
            throw new IndexSizeError();
        }
        $head = mb_substr($this->value, 0, $offset, 'utf-8');
        $tail = mb_substr($this->value, $offset + $count, null, 'utf-8');
        $this->setData($head . $data . $tail);
    }

    public function setData(string $data): void
    {
        $this->value = $data;
        $this->length = mb_strlen($data, 'utf-8');
    }

    public function getData(): string
    {
        return $this->value ?? '';
    }

    public function getLength(): int
    {
        return $this->length;
    }
}