<?php declare(strict_types=1);

namespace Souplette\Dom\Node;

use Souplette\Dom\Api\ChildNodeInterface;
use Souplette\Dom\Api\NodeInterface;
use Souplette\Dom\Internal\PropertyMaps;
use Souplette\Dom\Traits\ChildNodeTrait;
use Souplette\Dom\Traits\NodeTrait;

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
