<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Tokenizer;

/**
 * @see https://www.w3.org/TR/css-syntax-3/#tokenization
 */
final class TokenTypes
{
    const EOF = 0;
    const IDENT = 1;
    const FUNCTION = 2;
    const AT_KEYWORD = 3;
    const HASH = 4;
    const STRING = 5;
    const BAD_STRING = 6;
    const URL = 7;
    const BAD_URL = 8;
    const DELIM = 9;
    const NUMBER = 10;
    const PERCENTAGE = 11;
    const DIMENSION = 12;
    const WHITESPACE = 13;
    const CDO = 14;
    const CDC = 15;
    const COLON = 16;
    const SEMICOLON = 17;
    const COMMA = 18;
    const LBRACK = 19;
    const RBRACK = 20;
    const LPAREN = 21;
    const RPAREN = 22;
    const LCURLY = 23;
    const RCURLY = 24;

    const NAMES = [
        self::EOF => 'EOF',
        self::IDENT => 'IDENT',
        self::FUNCTION => 'FUNCTION',
        self::AT_KEYWORD => 'AT_KEYWORD',
        self::HASH => 'HASH',
        self::STRING => 'STRING',
        self::BAD_STRING => 'BAD_STRING',
        self::URL => 'URL',
        self::BAD_URL => 'BAD_URL',
        self::DELIM => 'DELIM',
        self::NUMBER => 'NUMBER',
        self::PERCENTAGE => 'PERCENTAGE',
        self::DIMENSION => 'DIMENSION',
        self::WHITESPACE => 'WHITESPACE',
        self::CDO => 'CDO',
        self::CDC => 'CDC',
        self::COLON => 'COLON',
        self::SEMICOLON => 'SEMICOLON',
        self::COMMA => 'COMMA',
        self::LBRACK => 'LBRACK',
        self::RBRACK => 'RBRACK',
        self::LPAREN => 'LPAREN',
        self::RPAREN => 'RPAREN',
        self::LCURLY => 'LCURLY',
        self::RCURLY => 'RCURLY',
    ];

    public static function nameOf(int $type): string
    {
        return self::NAMES[$type];
    }
}
