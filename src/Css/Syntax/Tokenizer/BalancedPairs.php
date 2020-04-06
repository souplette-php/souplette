<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Tokenizer;

final class BalancedPairs
{
    const START_TOKENS = [
        TokenTypes::LCURLY => TokenTypes::RCURLY,
        TokenTypes::LBRACK => TokenTypes::RBRACK,
        TokenTypes::LPAREN => TokenTypes::RPAREN,
        TokenTypes::FUNCTION => TokenTypes::RPAREN,
        TokenTypes::URL => TokenTypes::RPAREN,
    ];
    const END_TOKENS = [
        TokenTypes::RCURLY => true,
        TokenTypes::RBRACK => true,
        TokenTypes::RPAREN => true,
    ];
}
