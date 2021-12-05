<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\PseudoClass;

use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Dom\Element;
use Souplette\Dom\Node;
use Souplette\Dom\Traversal\ElementTraversal;

/**
 * @see https://drafts.csswg.org/selectors-4/#empty-pseudo
 */
final class EmptyPseudo extends PseudoClassSelector
{
    public function matches(QueryContext $context, Element $element): bool
    {
        foreach (ElementTraversal::childrenOf($element) as $child) {
            $isEmpty = match ($child->nodeType) {
                Node::ELEMENT_NODE => false,
                Node::TEXT_NODE, Node::CDATA_SECTION_NODE => $this->isWhitespace($child->data),
                default => true,
            };
            if (!$isEmpty) return false;
        }
        return true;
    }

    private function isWhitespace(string $data): bool
    {
        return !$data || strspn($data, " \n\r\f\t") === \strlen($data);
    }
}
