<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\PseudoClass;

use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Css\Selectors\Query\FormMatcher;
use Souplette\Css\Selectors\Query\QueryContext;

/**
 * @see https://drafts.csswg.org/selectors-4/#enableddisabled
 * @see https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#concept-fe-disabled
 */
final class DisabledPseudo extends PseudoClassSelector
{
    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        return FormMatcher::isDisabled($element, $context);
    }
}
