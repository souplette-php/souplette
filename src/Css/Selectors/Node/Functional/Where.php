<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Functional;

use Souplette\Css\Selectors\Node\FunctionalSelector;
use Souplette\Css\Selectors\Node\SelectorList;

final class Where extends FunctionalSelector
{
    public function __construct(SelectorList $selectors)
    {
        parent::__construct('where', [$selectors]);
    }

    public function __toString()
    {
        return ":where({$this->arguments[0]})";
    }
}
