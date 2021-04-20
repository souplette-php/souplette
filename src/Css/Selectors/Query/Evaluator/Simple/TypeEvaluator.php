<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Query\Evaluator\Simple;

use Souplette\Css\Selectors\Query\EvaluatorInterface;
use Souplette\Css\Selectors\Query\QueryContext;

final class TypeEvaluator implements EvaluatorInterface
{
    public function __construct(
        public string $localName,
        public ?string $namespace = null,
    ) {
    }

    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        return match($this->namespace) {
            '*' => self::hasLocalName($element, $this->localName),
            null => !$element->namespaceURI && self::hasLocalName($element, $this->localName),
            default => self::hasLocalName($element, $this->localName) && $element->prefix === $this->namespace,
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
