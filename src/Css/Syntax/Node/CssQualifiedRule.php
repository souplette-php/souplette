<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Node;

use Souplette\Css\Syntax\Tokenizer\Token;

final class CssQualifiedRule extends CssRule
{
    /**
     * @param CssValue|Token[] $prelude
     * @param CssSimpleBlock|null $body
     */
    public function __construct(
        public array $prelude = [],
        public ?CssSimpleBlock $body = null,
    ) {
    }
}
