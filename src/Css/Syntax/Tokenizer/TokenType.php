<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\Tokenizer;

/**
 * @see https://www.w3.org/TR/css-syntax-3/#tokenization
 */
enum TokenType
{
    case INVALID;
    case EOF;
    case IDENT;
    case FUNCTION;
    case AT_KEYWORD;
    case HASH;
    case STRING;
    case BAD_STRING;
    case URL;
    case BAD_URL;
    case DELIM;
    case NUMBER;
    case PERCENTAGE;
    case DIMENSION;
    case WHITESPACE;
    case CDO;
    case CDC;
    case COLON;
    case SEMICOLON;
    case COMMA;
    case LBRACK;
    case RBRACK;
    case LPAREN;
    case RPAREN;
    case LCURLY;
    case RCURLY;
}
