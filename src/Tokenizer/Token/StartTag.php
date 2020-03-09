<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tokenizer\Token;

use ju1ius\HtmlParser\Tokenizer\TokenTypes;

final class StartTag extends Tag
{
    /**
     * @var int
     */
    public $type = TokenTypes::START_TAG;
    /**
     * @var bool
     * @see @see https://html.spec.whatwg.org/multipage/parsing.html#acknowledge-self-closing-flag
     */
    public $selfClosingAcknowledged = false;
}
