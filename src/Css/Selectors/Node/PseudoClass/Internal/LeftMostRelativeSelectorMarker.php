<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\PseudoClass\Internal;

use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Selectors\Specificity;

final class LeftMostRelativeSelectorMarker extends PseudoClassSelector
{
    public function __construct()
    {
        parent::__construct('-internal-relative-leftmost');
    }

    public function __toString(): string
    {
        return '';
    }

    public function getSpecificity(): Specificity
    {
        return new Specificity(0, 0, 0);
    }

    public function matches(QueryContext $context, \DOMElement $element): bool
    {
        return $context->relativeLeftMostElement === $element;
    }
}
