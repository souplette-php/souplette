<?php declare(strict_types=1);

namespace Souplette\Dom\Legacy;

use Souplette\Dom\Legacy\Api\NodeInterface;
use Souplette\Dom\Legacy\Traits\NodeTrait;

/**
 * @property-read Element $ownerElement
 */
final class Attr extends \DOMAttr implements NodeInterface
{
    use NodeTrait;
}
