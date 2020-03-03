<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Parser;

final class TokenTypes
{
    const EOF = -1;
    const DOCTYPE = 1;
    const START_TAG = 2;
    const END_TAG = 3;
    const COMMENT = 4;
    const CHARACTER = 5;

    private const NAMES = [
        self::EOF => 'EOF',
        self::DOCTYPE => 'DOCTYPE',
        self::START_TAG => 'START_TAG',
        self::END_TAG => 'END_TAG',
        self::COMMENT => 'COMMENT',
        self::CHARACTER => 'CHARACTER',
    ];

    public static function name(int $type): string
    {
        if (!isset(self::NAMES[$type])) {
            throw new \UnexpectedValueException("Unknown token type: {$type}");
        }

        return self::NAMES[$type];
    }
}
