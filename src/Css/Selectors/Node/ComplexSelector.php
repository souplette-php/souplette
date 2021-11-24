<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Selectors\Specificity;

/**
 * TODO: can we remove this class ?
 */
final class ComplexSelector extends Selector implements \IteratorAggregate
{
    public function __construct(
        public SimpleSelector $selector,
    ) {
    }

    /**
     * @return iterable<SimpleSelector>
     */
    public function simpleSelectors(): iterable
    {
        yield from $this->getIterator();
    }

    /**
     * @return iterable<SimpleSelector>
     */
    public function getIterator(): iterable
    {
        for ($selector = $this->selector; $selector; $selector = $selector->next) {
            yield $selector;
        }
    }

    public function __toString(): string
    {
        $css = '';
        for ($selector = $this->selector; $selector; $selector = $selector->next) {
            [$selector, $compound] = $this->serializeCompound($selector);
            if (!$selector) return $compound . $css;
            $css = RelationType::toCss($selector->relationType) . $selector . $css;
        }
        return $css;
    }

    public function getSpecificity(): Specificity
    {
        $spec = new Specificity();
        for ($selector = $this->selector; $selector; $selector = $selector->next) {
            $spec = $spec->add($selector->getSpecificity());
        }
        return $spec;
    }

    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        return $this->matchSelector($this->selector, $context, $element);
    }

    private function matchSelector(SimpleSelector $selector, QueryContext $context, \DOMElement $element)
    {
        if (!$selector->matches($context, $element)) {
            return false;
        }
        if (!$selector->next) {
            return true;
        }

        return match ($selector->relationType) {
            RelationType::COLUMN => false, // not supported
            RelationType::SUB => $this->matchSelector($selector->next, $context, $element),
            default => $this->matchRelation($selector, $context, $element),
        };
    }

    private function matchRelation(SimpleSelector $selector, QueryContext $context, \DOMElement $element)
    {
        $relationType = $selector->relationType;
        $nextSelector = $selector->next;
        switch ($relationType) {
            case RelationType::CHILD:
                $parent = $element->parentNode;
                if (!$parent) return false;
                return $this->matchSelector($nextSelector, $context, $parent);
            case RelationType::NEXT:
                $previous = $element->previousElementSibling;
                if (!$previous) return false;
                return $this->matchSelector($nextSelector, $context, $previous);
            case RelationType::DESCENDANT:
                $parent = $element->parentNode;
                while ($parent) {
                    if ($this->matchSelector($nextSelector, $context, $parent)) return true;
                    $parent = $parent->parentNode;
                }
                return false;
            case RelationType::FOLLOWING:
                $previous = $element->previousElementSibling;
                while ($previous) {
                    if ($this->matchSelector($nextSelector, $context, $previous)) return true;
                    $previous = $previous->previousElementSibling;
                }
                return false;
            default:
                break;
        }
        return false;
    }

    /**
     * @param SimpleSelector $selector
     * @return array{SimpleSelector|null, string}
     */
    private function serializeCompound(SimpleSelector $selector): array
    {
        $css = '';
        for ($current = $selector; $current; $current = $current->next) {
            $css .= $current;
            if ($current->relationType !== RelationType::SUB) {
                return [$current, $css];
            }
        }
        return [$current, $css];
    }
}
