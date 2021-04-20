<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\Simple;

use Souplette\Css\Selectors\Query\Evaluator\AlwaysMatches;
use Souplette\Css\Selectors\Query\EvaluatorInterface;

final class UniversalEvaluator implements EvaluatorInterface
{
    use AlwaysMatches;
}
