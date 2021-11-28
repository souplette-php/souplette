<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Node;

final class CssDeclaration extends CssRule
{
    public function __construct(
        public string $name,
        /** @var CssValue[] */
        public array $body = [],
        public bool $important = false,
    ) {
    }
}
