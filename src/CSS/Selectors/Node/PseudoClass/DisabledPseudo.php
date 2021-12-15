<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node\PseudoClass;

use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\CSS\Selectors\Query\FormMatcher;
use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\DOM\Element;

/**
 * @see https://drafts.csswg.org/selectors-4/#enableddisabled
 * @see https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#concept-fe-disabled
 */
final class DisabledPseudo extends PseudoClassSelector
{
    public function matches(QueryContext $context, Element $element): bool
    {
        return FormMatcher::isDisabled($element, $context);
    }
}
