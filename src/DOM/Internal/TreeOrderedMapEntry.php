<?php declare(strict_types=1);

namespace Souplette\DOM\Internal;

use Souplette\DOM\Element;

/**
 * @internal
 */
final class TreeOrderedMapEntry
{
    public ?Element $element = null;
    public int $count;
    public array $orderedList;

    public function __construct(Element $element)
    {
        $this->element = $element;
        $this->orderedList = [$element];
        $this->count = 1;
    }
}
