<?php declare(strict_types=1);

namespace Souplette\Html\Parser\Tokenizer;

final class TokenTypes
{
    const UNKNOWN = -1;
    const EOF = 0;
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

    /**
     * Convenience method for printing a token type.
     *
     * @codeCoverageIgnore
     */
    public static function nameOf(Token|int $tokenOrType): string
    {
        if ($tokenOrType instanceof Token) {
            $type = $tokenOrType::TYPE;
        } else {
            $type = (int)$tokenOrType;
        }

        if (!isset(self::NAMES[$type])) {
            throw new \UnexpectedValueException("Unknown token type: {$type}");
        }

        return self::NAMES[$type];
    }
}
