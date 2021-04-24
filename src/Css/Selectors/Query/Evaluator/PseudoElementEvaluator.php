<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator;

use Souplette\Css\Selectors\Query\EvaluatorInterface;

final class PseudoElementEvaluator implements EvaluatorInterface
{
    use NeverMatches;
}
