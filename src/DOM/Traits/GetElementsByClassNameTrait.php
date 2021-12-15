<?php declare(strict_types=1);

namespace Souplette\DOM\Traits;

use Souplette\CSS\Selectors\SelectorQuery;

trait GetElementsByClassNameTrait
{
    public function getElementsByClassName(string $classNames): array
    {
        return SelectorQuery::byClassNames($this, $classNames);
    }
}
