<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom;

use JoliPotage\Html\Dom\Api\HtmlNodeInterface;
use JoliPotage\Html\Dom\Api\NonDocumentTypeChildNodeInterface;
use JoliPotage\Html\Dom\Traits\NonDocumentTypeChildNodeTrait;

class HtmlNode extends \DOMNode implements HtmlNodeInterface, NonDocumentTypeChildNodeInterface
{
    use NonDocumentTypeChildNodeTrait;

    public function __get($name)
    {
        $method = PropertyMaps::READ[NonDocumentTypeChildNodeInterface::class][$name] ?? null;
        if ($method) {
            return $this->{$method}();
        }
    }
}
