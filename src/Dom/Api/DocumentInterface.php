<?php declare(strict_types=1);

namespace Souplette\Dom\Api;

use Souplette\Dom\Api\Native\DomDocumentInterface;
use Souplette\Dom\Legacy\Element;
use Souplette\Dom\Legacy\Implementation;

/**
 * @see https://dom.spec.whatwg.org/#document
 *
 * @property-read Implementation $implementation
 * @property-read Element|null $documentElement
 * @property-read string $mode
 * @property-read string $compatMode
 * @property-read Element|null $head
 * @property-read Element|null $body
 * @property string $title
 */
interface DocumentInterface extends NodeInterface, ParentNodeInterface, NonElementParentNodeInterface, DomDocumentInterface
{
    public function getMode(): string;
    public function getCompatMode(): string;
    public function getHead(): ?Element;
    public function getBody(): ?Element;
    public function getTitle(): string;
    public function setTitle(string $title): void;
    /**
     * @param string $classNames
     * @return Element[]
     */
    public function getElementsByClassName(string $classNames): array;
}
