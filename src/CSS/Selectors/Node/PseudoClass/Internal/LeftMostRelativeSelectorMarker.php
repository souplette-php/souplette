<?php declare(strict_types=1);

namespace Souplette\CSS\Selectors\Node\PseudoClass\Internal;

use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\CSS\Selectors\Specificity;
use Souplette\DOM\Element;

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

    public function matches(QueryContext $context, Element $element): bool
    {
        return $context->relativeLeftMostElement === $element;
    }
}
