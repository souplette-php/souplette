<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\Simple;

use Souplette\Css\Selectors\Node\Simple\TypeSelector;
use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\QueryContext;

final class TypeEvaluator implements EvaluatorInterface
{
    public function matches(QueryContext $context): bool
    {
        $selector = $context->selector;
        assert($selector instanceof TypeSelector);
        $element = $context->element;

        return match($selector->namespace) {
            '*' => self::hasLocalName($element, $selector->tagName),
            null => !$element->namespaceURI && self::hasLocalName($element, $selector->tagName),
            default => self::hasLocalName($element, $selector->tagName) && $element->prefix === $selector->namespace,
        };
    }

    public static function isSameType(\DOMElement $a, \DOMElement $b): bool
    {
        return self::hasLocalName($a, $b->localName);
    }

    public static function hasLocalName(\DOMElement $element, string $name): bool
    {
        return strcasecmp($element->localName, $name) === 0;
    }
}
