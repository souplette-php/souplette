<?php declare(strict_types=1);

namespace Souplette\Html\Dom\Api;

use DOMNodeList;
use Souplette\Html\Dom\TokenList;

/**
 * @property string $id
 * @property string $className
 * @property string $innerHTML
 * @property string $outerHTML
 * @property-read TokenList $classList
 */
interface HtmlElementInterface extends DomElementInterface
{
    public function getId(): string;
    public function setId(string $id): void;
    public function getClassName(): string;
    public function setClassName(string $className): void;
    public function getInnerHTML(): string;
    public function setInnerHTML(string $html): void;
    public function getOuterHTML(): string;
    public function setOuterHTML(string $html): void;
    public function getClassList(): TokenList;
    public function getElementsByClassName(string $classNames): DOMNodeList;
}
