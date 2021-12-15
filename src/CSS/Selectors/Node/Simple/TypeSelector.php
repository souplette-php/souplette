<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node\Simple;

use Souplette\CSS\Selectors\Namespaces;
use Souplette\CSS\Selectors\Node\SimpleSelector;
use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\CSS\Selectors\Specificity;
use Souplette\DOM\Element;

/**
 * @see https://drafts.csswg.org/selectors-4/#type-selectors
 */
class TypeSelector extends SimpleSelector
{
    public function __construct(
        public string $tagName,
        public ?string $namespace = null
    ) {
    }

    public function __toString(): string
    {
        // @see https://drafts.csswg.org/selectors/#type-nmsp
        return match($this->namespace) {
            Namespaces::NONE => "|{$this->tagName}",
            Namespaces::ANY => $this->tagName,
            // TODO: default namespace must be resolved
            Namespaces::DEFAULT => "*|{$this->tagName}",
            default => "{$this->namespace}|{$this->tagName}"
        };
    }

    public function getSpecificity(): Specificity
    {
        return new Specificity(0, 0, 1);
    }

    public function matches(QueryContext $context, Element $element): bool
    {
        return match ($this->namespace) {
            '*' => $this->matchesLocalName($element, $context),
            null => !$element->namespaceURI && $this->matchesLocalName($element, $context),
            default => $element->namespaceURI === $this->namespace && $this->matchesLocalName($element, $context),
        };
    }

    private function matchesLocalName(Element $element, QueryContext $ctx): bool
    {
        return match ($ctx->caseInsensitiveTypes) {
            true => strcasecmp($element->localName, $this->tagName) === 0,
            false => $element->localName === $this->tagName,
        };
    }
}
