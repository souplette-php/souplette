<?php declare(strict_types=1);

namespace Souplette\HTML\DOM\Traits;

use Souplette\DOM\Collections\ElementDataSet;

trait HTMLOrSVGElementTrait
{
    protected ?ElementDataSet $dataSet = null;

    public function getDataset(): ElementDataSet
    {
        return $this->dataSet ??= new ElementDataSet($this);
    }
}
