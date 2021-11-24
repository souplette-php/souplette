<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

abstract class SimpleSelector extends Selector
{
    public RelationType $relationType = RelationType::SUB;
    public ?SimpleSelector $next = null;

    public function simpleSelectors(): iterable
    {
        yield $this;
    }

    public function append(SimpleSelector $selector, RelationType $relation = RelationType::SUB): static
    {
        $end = $this;
        while ($end->next) $end = $end->next;
        $end->relationType = $relation;
        $end->next = $selector;
        return $this;
    }
}
