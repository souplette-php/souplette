<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Node;

final class CssStylesheet
{
    /**
     * @param CssRule[] $rules
     */
    public function __construct(
        public array $rules = [],
    ) {
    }
}
