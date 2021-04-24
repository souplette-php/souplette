<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\PseudoClass;

use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\QueryContext;

final class DefaultEvaluator implements EvaluatorInterface
{
    private const INPUT_TYPES = [
        'checkbox' => true,
        'radio' => true,
    ];

    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        $type = $context->caseInsensitiveTypes ? strtolower($element->localName) : $element->localName;
        return match ($type) {
            'input' => $element->hasAttribute('checked')
                && self::INPUT_TYPES[$element->getAttribute('type')] ?? false,
            'option' => $element->hasAttribute('selected'),
            default => false,
        };
    }
}
