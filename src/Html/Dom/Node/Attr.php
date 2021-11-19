<?php declare(strict_types=1);

namespace Souplette\Html\Dom\Node;

use Souplette\Html\Dom\Api\NodeInterface;
use Souplette\Html\Dom\Traits\NodeTrait;

final class Attr extends \DOMAttr implements NodeInterface
{
    use NodeTrait;
}
