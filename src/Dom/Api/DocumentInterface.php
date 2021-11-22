<?php declare(strict_types=1);

namespace Souplette\Dom\Api;

use DOMElement;
use Souplette\Dom\Node\Element;

/**
 * @see https://dom.spec.whatwg.org/#document
 *
 * @property-read string $mode
 * @property-read string $compatMode
 * @property-read DOMElement $head
 * @property-read DOMElement $body
 * @property string $title
 */
interface DocumentInterface extends NodeInterface
{
    public function getMode(): string;
    public function getCompatMode(): string;
    public function getHead(): ?DOMElement;
    public function getBody(): ?DOMElement;
    public function getTitle(): string;
    public function setTitle(string $title): void;
    /**
     * @param string $classNames
     * @return Element[]
     */
    public function getElementsByClassName(string $classNames): array;
}
