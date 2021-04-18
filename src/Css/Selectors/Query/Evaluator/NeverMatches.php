<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator;

use Souplette\Css\Selectors\Query\QueryContext;

trait NeverMatches
{
    public function matches(QueryContext $context): bool
    {
        return false;
    }
}
