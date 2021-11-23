<?php declare(strict_types=1);

namespace Souplette\Dom\Api;

use Souplette\Dom\Element;
use Souplette\Dom\HtmlElement;

/**
 * @see https://dom.spec.whatwg.org/#document
 *
 * @property-read string $mode
 * @property-read string $compatMode
 * @property-read HtmlElement $head
 * @property-read HtmlElement $body
 * @property string $title
 */
interface DocumentInterface extends NodeInterface
{
    public function getMode(): string;
    public function getCompatMode(): string;
    public function getHead(): ?HtmlElement;
    public function getBody(): ?HtmlElement;
    public function getTitle(): string;
    public function setTitle(string $title): void;
    /**
     * @param string $classNames
     * @return Element[]
     */
    public function getElementsByClassName(string $classNames): array;
}
