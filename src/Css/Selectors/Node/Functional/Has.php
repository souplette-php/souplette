<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Functional;

use Souplette\Css\Selectors\Node\FunctionalSelector;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Selectors\Specificity;
use Souplette\Dom\ElementIterator;

final class Has extends FunctionalSelector
{
    public function __construct(public SelectorList $selectorList)
    {
        parent::__construct('has', [$this->selectorList]);
    }

    public function simpleSelectors(): iterable
    {
        yield $this;
        yield from $this->selectorList;
    }

    public function __toString(): string
    {
        return ":has({$this->selectorList})";
    }

    public function getSpecificity(): Specificity
    {
        return $this->selectorList->getSpecificity();
    }

    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        $subContext = $context->withScope($element);
        foreach (ElementIterator::descendants($element) as $candidate) {
            if ($this->selectorList->matches($subContext, $candidate)) {
                return true;
            }
        }
        return false;
    }
}
