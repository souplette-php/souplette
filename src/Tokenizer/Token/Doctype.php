<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tokenizer\Token;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenTypes;

final class Doctype extends Token
{
    /**
     * @var int
     */
    public $type = TokenTypes::DOCTYPE;
    /**
     * @var string
     */
    public $name = '';
    /**
     * @var string|null
     */
    public $publicIdentifier;
    /**
     * @var string|null
     */
    public $systemIdentifier;
    /**
     * @var bool
     */
    public $forceQuirks = false;
}
