<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tokenizer\Token;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenTypes;

final class EndTag extends Token
{
    public $type = TokenTypes::START_TAG;
    /**
     * @var string
     */
    public $name = '';
    /**
     * @var array|null
     */
    public $attributes;
}
