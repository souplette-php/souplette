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
        if (isset(NonDocumentTypeChildNodeInterface::PROPERTIES_READ[$name])) {
            $method = NonDocumentTypeChildNodeInterface::PROPERTIES_READ[$name];
            return $this->{$method}();
        }
    }
}
