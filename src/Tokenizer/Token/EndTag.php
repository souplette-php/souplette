<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tokenizer\Token;

use ju1ius\HtmlParser\Tokenizer\TokenTypes;

final class EndTag extends Tag
{
    /**
     * @var int
     */
    public $type = TokenTypes::END_TAG;
}
