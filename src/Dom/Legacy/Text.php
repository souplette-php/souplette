<?php declare(strict_types=1);

namespace Souplette\Dom\Legacy;

use Souplette\Dom\Legacy\Api\ChildNodeInterface;
use Souplette\Dom\Legacy\Api\NodeInterface;
use Souplette\Dom\Legacy\Internal\PropertyMaps;
use Souplette\Dom\Legacy\Traits\ChildNodeTrait;
use Souplette\Dom\Legacy\Traits\NodeTrait;

final class Text extends \DOMText implements
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
