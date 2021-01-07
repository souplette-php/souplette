<?php declare(strict_types=1);

namespace Souplette\Html\Dom\Node;

use Souplette\Html\Dom\Api\ChildNodeInterface;
use Souplette\Html\Dom\Api\HtmlNodeInterface;
use Souplette\Html\Dom\PropertyMaps;
use Souplette\Html\Dom\Traits\ChildNodeTrait;
use Souplette\Html\Dom\Traits\HtmlNodeTrait;

final class HtmlComment extends \DOMComment implements
    HtmlNodeInterface,
    ChildNodeInterface
{
    use HtmlNodeTrait;
    use ChildNodeTrait;

    public function __get($name)
    {
        return PropertyMaps::get($this, $name);
    }
}
