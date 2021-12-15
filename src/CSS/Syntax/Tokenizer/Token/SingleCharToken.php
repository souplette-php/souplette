<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\Tokenizer\Token;

use Souplette\CSS\Syntax\Tokenizer\Token;

abstract class SingleCharToken extends Token
{
    public string $value;

    const CHARS = [
        '(' => LeftParen::class,
        ')' => RightParen::class,
        '[' => LeftBracket::class,
        ']' => RightBracket::class,
        '{' => LeftCurly::class,
        '}' => RightCurly::class,
        ',' => Comma::class,
        ';' => SemiColon::class,
        ':' => Colon::class,
    ];

    public function __construct(int $position)
    {
        $this->position = $position;
    }
}
