<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\Simple;

use Souplette\Css\Selectors\Node\Simple\ClassSelector;
use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\Helper\AttributeMatchHelper;
use Souplette\Css\Selectors\Query\QueryContext;

final class ClassEvaluator implements EvaluatorInterface
{
    public function matches(QueryContext $context): bool
    {
        $selector = $context->selector;
        assert($selector instanceof ClassSelector);
        $className = $context->element->getAttribute('class');
        if (!$className) {
            return false;
        }

        return AttributeMatchHelper::includes($selector->class, $className, $context->caseInsensitiveClasses);
    }
}
