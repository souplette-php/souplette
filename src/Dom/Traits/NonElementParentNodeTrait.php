<?php declare(strict_types=1);

namespace Souplette\Dom\Traits;

use Souplette\Css\Selectors\SelectorQuery;
use Souplette\Dom\Element;

/**
 * @see https://dom.spec.whatwg.org/#interface-nonelementparentnode
 */
trait NonElementParentNodeTrait
{
    public function getElementById(string $elementId): ?Element
    {
        return SelectorQuery::byId($this, $elementId);
    }
}
