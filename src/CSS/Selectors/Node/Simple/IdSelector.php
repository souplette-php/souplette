<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node\Simple;

use Souplette\CSS\Selectors\Node\SimpleSelector;
use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\CSS\Selectors\Specificity;
use Souplette\DOM\Element;

final class IdSelector extends SimpleSelector
{
    public function __construct(
        public string $id,
    ) {
    }

    public function __toString(): string
    {
        return "#{$this->id}";
    }

    public function getSpecificity(): Specificity
    {
        return new Specificity(1);
    }

    public function matches(QueryContext $context, Element $element): bool
    {
        $id = $element->getAttribute('id');
        return match ($context->caseInsensitiveIds) {
            true => strcasecmp($id, $this->id) === 0,
            false => $this->id === $id,
        };
    }
}
