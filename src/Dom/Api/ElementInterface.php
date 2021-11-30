<?php declare(strict_types=1);

namespace Souplette\Dom\Api;


use Souplette\Dom\Api\Native\DomElementInterface;
use Souplette\Dom\Attr;
use Souplette\Dom\Element;
use Souplette\Dom\TokenList;

/**
 * @property string $id
 * @property string $className
 * @property-read TokenList $classList
 * @property string $innerHTML
 * @property string $outerHTML
 */
interface ElementInterface extends DomElementInterface
{
    /**
     * @return Attr|false
     */
    public function getAttributeNode(string $qualifiedName);
    /**
     * @return Attr|false
     */
    public function setAttribute(string $qualifiedName, string $value): Attr|bool;
    /**
     * @return Attr|false|null
     */
    public function setAttributeNode(\DOMAttr $attr);
    /**
     * @return Attr|false|null
     */
    public function setAttributeNodeNS(\DOMAttr $attr);

    /**
     * If force is not given, "toggles" qualifiedName, removing it if it is present and adding it if it is not present.
     * If force is true, adds qualifiedName.
     * If force is false, removes qualifiedName.
     * Returns true if qualifiedName is now present; otherwise false.
     */
    public function toggleAttribute(string $qualifiedName, bool $force = null): bool;

    public function getId(): string;
    public function setId(string $id): void;

    public function getClassName(): string;
    public function setClassName(string $className): void;

    public function getClassList(): TokenList;

    /**
     * @param string $classNames
     * @return Element[]
     */
    public function getElementsByClassName(string $classNames): array;

    public function getInnerHTML(): string;
    public function setInnerHTML(string $html): void;

    public function getOuterHTML(): string;
    public function setOuterHTML(string $html): void;

    public function insertAdjacentHTML(string $position, string $html): void;

    public function matches(string $selector): bool;
    public function closest(string $selector): ?Element;
}
