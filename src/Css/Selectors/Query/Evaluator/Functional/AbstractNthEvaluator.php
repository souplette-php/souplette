<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\Functional;

use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\QueryContext;

abstract class AbstractNthEvaluator implements EvaluatorInterface
{
    public function __construct(
        protected int $a,
        protected int $b,
        protected ?EvaluatorInterface $filter = null,
    ) {
    }

    final public function matches(QueryContext $context, \DOMElement $element): bool
    {
        if ($this->filter && !$this->filter->matches($context, $element)) {
            return false;
        }
        $index = $this->getChildIndex($context, $element);
        return self::indexMatchesAnPlusB($index, $this->a, $this->b);
    }

    abstract protected function getChildIndex(QueryContext $context, \DOMElement $element): int;

    private static function indexMatchesAnPlusB(int $index, int $a, int $b): bool
    {
        if ($a === 0) {
            return $index === $b;
        }
        if ($a > 0) {
            if ($index < $b) return false;
            return ($index - $b) % $a === 0;
        }
        if ($index > $b) return false;
        return ($b - $index) % -($a) === 0;
    }
}
