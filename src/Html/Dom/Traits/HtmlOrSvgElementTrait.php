<?php declare(strict_types=1);

namespace Souplette\Html\Dom\Traits;

use Souplette\Dom\Collections\ElementDataSet;

trait HtmlOrSvgElementTrait
{
    protected ?ElementDataSet $dataSet = null;

    public function getDataset(): ElementDataSet
    {
        return $this->dataSet ??= new ElementDataSet($this);
    }
}
