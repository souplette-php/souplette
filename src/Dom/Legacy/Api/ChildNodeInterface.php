<?php declare(strict_types=1);

namespace Souplette\Dom\Legacy\Api;

use Souplette\Dom\Legacy\Element;

/**
 * @see https://dom.spec.whatwg.org/#interface-childnode
 *
 * @property-read Element|null $nextElementSibling
 * @property-read Element|null $previousElementSibling
 */
interface ChildNodeInterface extends \DOMChildNode
{
}
