<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node\Simple;

use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\CSS\Selectors\Specificity;
use Souplette\DOM\Element;

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

    public function matches(QueryContext $context, Element $element): bool
    {
        return true;
    }
}
