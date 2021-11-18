<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

abstract class SimpleSelector extends Selector
{
    public function simpleSelectors(): iterable
    {
        yield $this;
    }
}
