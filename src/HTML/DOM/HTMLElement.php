<?php declare(strict_types=1);

namespace Souplette\HTML\DOM;

use Souplette\DOM\Collections\ElementDataSet;
use Souplette\DOM\Element;
use Souplette\HTML\DOM\Traits\HTMLOrSVGElementTrait;

/**
 * @property-read ElementDataSet $dataset
 */
class HTMLElement extends Element
{
    use HTMLOrSVGElementTrait;

    public function __get(string $prop): mixed
    {
        return match ($prop) {
            'dataset' => $this->getDataset(),
            default => parent::__get($prop),
        };
    }
}
