<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Functional;

use Souplette\Css\Selectors\Node\FunctionalSelector;
use Souplette\Css\Selectors\Node\SelectorList;

final class Not extends FunctionalSelector
{
    public function __construct(SelectorList $selectors)
    {
        parent::__construct('not', [$selectors]);
    }

    public function __toString()
    {
        return ":not({$this->arguments[0]})";
    }
}
