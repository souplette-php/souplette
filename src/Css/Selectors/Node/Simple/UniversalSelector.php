<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Simple;

use Souplette\Css\Selectors\Specificity;

final class UniversalSelector extends TypeSelector
{
    public function __construct(?string $namespace = null)
    {
        parent::__construct('*', $namespace);
    }

    public function getSpecificity(): Specificity
    {
        return new Specificity();
    }
}
