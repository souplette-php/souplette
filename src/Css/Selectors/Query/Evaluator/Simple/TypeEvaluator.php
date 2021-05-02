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
        return match ($this->namespace) {
            '*' => $this->matchesLocalName($element, $context),
            null => !$element->namespaceURI && $this->matchesLocalName($element, $context),
            default => $element->namespaceURI === $this->namespace && $this->matchesLocalName($element, $context),
        };
    }

    private function matchesLocalName(\DOMElement $element, QueryContext $ctx): bool
    {
        return match ($ctx->caseInsensitiveTypes) {
            true => strcasecmp($element->localName, $this->localName) === 0,
            false => $element->localName === $this->localName,
        };
    }
}
