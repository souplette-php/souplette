<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\Node;

final class CSSStylesheet
{
    /**
     * @param CSSRule[] $rules
     */
    public function __construct(
        public array $rules = [],
    ) {
    }
}
