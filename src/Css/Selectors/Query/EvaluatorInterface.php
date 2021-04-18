<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query;

interface EvaluatorInterface
{
    public function matches(QueryContext $context): bool;
}
