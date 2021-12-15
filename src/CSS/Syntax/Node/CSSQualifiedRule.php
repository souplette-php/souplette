<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\Node;

use Souplette\CSS\Syntax\Tokenizer\Token;

final class CSSQualifiedRule extends CSSRule
{
    /**
     * @param CSSValue|Token[] $prelude
     * @param CSSSimpleBlock|null $body
     */
    public function __construct(
        public array $prelude = [],
        public ?CSSSimpleBlock $body = null,
    ) {
    }
}
