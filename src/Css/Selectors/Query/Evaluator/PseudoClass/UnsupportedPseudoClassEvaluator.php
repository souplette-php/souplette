<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\PseudoClass;

use Souplette\Css\Selectors\Query\Evaluator\NeverMatches;
use Souplette\Css\Selectors\Query\EvaluatorInterface;

final class UnsupportedPseudoClassEvaluator implements EvaluatorInterface
{
    use NeverMatches;
}
