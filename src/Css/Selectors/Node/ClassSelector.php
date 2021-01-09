<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

use Souplette\Css\Selectors\Specificity;

final class ClassSelector extends SimpleSelector
{
    private string $class;

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    public function __toString()
    {
        return ".{$this->class}";
    }

    public function getSpecificity(): Specificity
    {
        return new Specificity(0, 1);
    }
}
