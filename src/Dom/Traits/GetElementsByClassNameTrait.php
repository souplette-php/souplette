<?php declare(strict_types=1);

namespace Souplette\Dom\Traits;

use Souplette\Css\Selectors\SelectorQuery;

trait GetElementsByClassNameTrait
{
    public function getElementsByClassName(string $classNames): array
    {
        return SelectorQuery::byClassNames($this, $classNames);
    }
}
