<?php declare(strict_types=1);

namespace Souplette\Dom;

use Souplette\Dom\Api\NodeInterface;
use Souplette\Dom\Traits\NodeTrait;

final class Attr extends \DOMAttr implements NodeInterface
{
    use NodeTrait;
}
