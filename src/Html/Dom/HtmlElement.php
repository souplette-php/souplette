<?php declare(strict_types=1);

namespace Souplette\Html\Dom;

use Souplette\Dom\Collections\ElementDataSet;
use Souplette\Dom\Element;
use Souplette\Html\Dom\Traits\HtmlOrSvgElementTrait;

/**
 * @property-read ElementDataSet $dataset
 */
class HtmlElement extends Element
{
    use HtmlOrSvgElementTrait;

    public function __get(string $prop): mixed
    {
        return match ($prop) {
            'dataset' => $this->getDataset(),
            default => parent::__get($prop),
        };
    }
}
