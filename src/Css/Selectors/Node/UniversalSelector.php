<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

final class UniversalSelector extends TypeSelector
{
    public function __construct(?string $namespace = null)
    {
        parent::__construct('*', $namespace);
    }
}
