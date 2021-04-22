<?php declare(strict_types=1);

namespace Souplette\Html\Dom\Api;

use DOMElement;
use DOMNodeList;
use Souplette\Html\Dom\Node\HtmlElement;

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
    /**
     * @param string $classNames
     * @return HtmlElement[]
     */
    public function getElementsByClassName(string $classNames): array;
}
