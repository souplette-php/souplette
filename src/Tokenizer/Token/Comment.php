<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tokenizer\Token;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenTypes;

final class Comment extends Token
{
    /**
     * @var int
     */
    public $type = TokenTypes::COMMENT;
    /**
     * @var string
     */
    public $data = '';

    public function __construct(string $data = '')
    {
        $this->data = $data;
    }
}
