<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Simple;

use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Selectors\Specificity;
use Souplette\Dom\Element;

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
