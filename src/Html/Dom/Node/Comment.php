<?php declare(strict_types=1);

namespace Souplette\Html\Dom\Node;

use Souplette\Html\Dom\Api\ChildNodeInterface;
use Souplette\Html\Dom\Api\NodeInterface;
use Souplette\Html\Dom\Internal\PropertyMaps;
use Souplette\Html\Dom\Traits\ChildNodeTrait;
use Souplette\Html\Dom\Traits\NodeTrait;

final class Comment extends \DOMComment implements
    NodeInterface,
    ChildNodeInterface
{
    use NodeTrait;
    use ChildNodeTrait;

    public function __get($name)
    {
        return PropertyMaps::get($this, $name);
    }
}
