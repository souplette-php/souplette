<?php declare(strict_types=1);

namespace Souplette\Dom\Api;

use Souplette\Dom\Element;

/**
 * @see https://dom.spec.whatwg.org/#interface-childnode
 * @property-read Element|null $nextElementSibling
 * @property-read Element|null $previousElementSibling
 */
interface ChildNodeInterface extends \DOMChildNode
{
}
