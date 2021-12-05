<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Simple;

use Souplette\Css\Selectors\Node\SimpleSelector;
use Souplette\Css\Selectors\Query\AttributeMatcher;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Selectors\Specificity;
use Souplette\Dom\Element;

final class ClassSelector extends SimpleSelector
{
    public function __construct(
        public string $class,
    ) {
    }

    public function __toString(): string
    {
        return ".{$this->class}";
    }

    public function getSpecificity(): Specificity
    {
        return new Specificity(0, 1);
    }

    public function matches(QueryContext $context, Element $element): bool
    {
        $className = $element->getAttribute('class');
        if (!$className) {
            return false;
        }

        return AttributeMatcher::includes($this->class, $className, $context->caseInsensitiveClasses);
    }
}
