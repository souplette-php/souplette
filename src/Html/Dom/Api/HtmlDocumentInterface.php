<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom\Api;

use DOMElement;
use DOMNodeList;

/**
 * @see https://dom.spec.whatwg.org/#document
 *
 * @property-read string $mode
 * @property-read string $compatMode
 * @property-read DOMElement $head
 * @property-read DOMElement $body
 * @property string $title
 */
interface HtmlDocumentInterface extends HtmlNodeInterface
{
    public function getMode(): string;
    public function getCompatMode(): string;
    public function getHead(): ?DOMElement;
    public function getBody(): ?DOMElement;
    public function getTitle(): string;
    public function setTitle(string $title): void;
    public function getElementsByClassName(string $classNames): DOMNodeList;
}
