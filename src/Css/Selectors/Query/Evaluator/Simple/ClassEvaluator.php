<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\Simple;

use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\Helper\AttributeMatchHelper;
use Souplette\Css\Selectors\Query\QueryContext;

final class ClassEvaluator implements EvaluatorInterface
{
    public function __construct(
        public string $class,
    ) {
    }

    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        $className = $element->getAttribute('class');
        if (!$className) {
            return false;
        }

        return AttributeMatchHelper::includes($this->class, $className, $context->caseInsensitiveClasses);
    }
}
