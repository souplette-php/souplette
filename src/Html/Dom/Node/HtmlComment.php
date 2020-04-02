<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom\Node;

use JoliPotage\Html\Dom\Api\ChildNodeInterface;
use JoliPotage\Html\Dom\Api\NonDocumentTypeChildNodeInterface;
use JoliPotage\Html\Dom\PropertyMaps;
use JoliPotage\Html\Dom\Traits\ChildNodeTrait;
use JoliPotage\Html\Dom\Traits\NonDocumentTypeChildNodeTrait;

final class HtmlComment extends \DOMComment implements
    NonDocumentTypeChildNodeInterface,
    ChildNodeInterface
{
    use NonDocumentTypeChildNodeTrait;
    use ChildNodeTrait;

    public function __get($name)
    {
        $method = PropertyMaps::READ[NonDocumentTypeChildNodeInterface::class][$name] ?? null;
        if ($method) {
            return $this->{$method}();
        }
    }
}
