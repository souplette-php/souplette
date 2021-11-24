<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Simple;

use Souplette\Css\Selectors\Node\SimpleSelector;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Selectors\Specificity;

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

    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        $id = $element->getAttribute('id');
        return match ($context->caseInsensitiveIds) {
            true => strcasecmp($id, $this->id) === 0,
            false => $this->id === $id,
        };
    }
}
