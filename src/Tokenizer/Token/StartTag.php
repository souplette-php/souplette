<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tokenizer\Token;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenTypes;

final class StartTag extends Token
{
    /**
     * @var int
     */
    public $type = TokenTypes::START_TAG;
    /**
     * @var string
     */
    public $name = '';
    /**
     * @var bool
     */
    public $selfClosing = false;
    /**
     * @var array|null
     */
    public $attributes;
}
