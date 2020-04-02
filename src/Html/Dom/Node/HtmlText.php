<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom\Node;

use JoliPotage\Html\Dom\Api\ChildNodeInterface;
use JoliPotage\Html\Dom\Api\HtmlNodeInterface;
use JoliPotage\Html\Dom\Api\NonDocumentTypeChildNodeInterface;
use JoliPotage\Html\Dom\PropertyMaps;
use JoliPotage\Html\Dom\Traits\ChildNodeTrait;
use JoliPotage\Html\Dom\Traits\HtmlNodeTrait;
use JoliPotage\Html\Dom\Traits\NonDocumentTypeChildNodeTrait;

final class HtmlText extends \DOMText implements
    HtmlNodeInterface,
    NonDocumentTypeChildNodeInterface,
    ChildNodeInterface
{
    use HtmlNodeTrait;
    use NonDocumentTypeChildNodeTrait;
    use ChildNodeTrait;

    public function __get($name)
    {
        return PropertyMaps::get($this, $name);
    }
}
