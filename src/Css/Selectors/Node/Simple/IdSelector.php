<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Simple;

use Souplette\Css\Selectors\Node\SimpleSelector;
use Souplette\Css\Selectors\Specificity;

final class IdSelector extends SimpleSelector
{
    public function __construct(public string $id)
    {
    }

    public function __toString(): string
    {
        return "#{$this->id}";
    }

    public function getSpecificity(): Specificity
    {
        return new Specificity(1);
    }
}
