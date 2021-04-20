<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Simple;

use Souplette\Css\Selectors\Node\SimpleSelector;
use Souplette\Css\Selectors\Specificity;

final class ClassSelector extends SimpleSelector
{
    public function __construct(public string $class)
    {
    }

    public function __toString(): string
    {
        return ".{$this->class}";
    }

    public function getSpecificity(): Specificity
    {
        return new Specificity(0, 1);
    }
}
