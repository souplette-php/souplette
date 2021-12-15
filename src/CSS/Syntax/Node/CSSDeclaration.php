<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\Node;

final class CSSDeclaration extends CSSRule
{
    public function __construct(
        public string $name,
        /** @var CSSValue[] */
        public array $body = [],
        public bool $important = false,
    ) {
    }
}
