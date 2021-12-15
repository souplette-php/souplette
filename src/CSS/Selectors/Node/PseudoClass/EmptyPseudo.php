<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node\PseudoClass;

use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\DOM\Element;
use Souplette\DOM\Node;
use Souplette\DOM\Traversal\ElementTraversal;

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
