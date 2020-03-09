<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tokenizer\Token;

use ju1ius\HtmlParser\Tokenizer\Token;

abstract class Tag extends Token
{
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

    public function __construct(string $name = '')
    {
        $this->name = $name;
    }
}
