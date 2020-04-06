<?php declare(strict_types=1);

namespace JoliPotage\Css\Parser;

use JoliPotage\Css\CssOm\UnicodeRange;
use JoliPotage\Css\Parser\Exception\ParseError;
use JoliPotage\Css\Parser\Tokenizer\TokenTypes;
use JoliPotage\Css\Parser\TokenStream\TokenStreamInterface;

/**
 * @see https://www.w3.org/TR/css-syntax-3/#urange
 */
final class UnicodeRangeParser
{
    private const URANGE_PATTERN = <<<'REGEXP'
/
    ^ U \+
    (?:
        (?<start> [a-f0-9]{1,6} ) (?: - (?<end> [a-f0-9]{1,6}) )?
        | (?<mask> [a-f0-9?]{1,6} )
    )
    $ 
/xi
REGEXP;

    private TokenStreamInterface $tokenStream;

    public function __construct(TokenStreamInterface $tokenStream)
    {
        $this->tokenStream = $tokenStream;
    }

    public function parse(): UnicodeRange
    {
        $text = $this->concatenateTokens();
        if (preg_match(self::URANGE_PATTERN, $text, $m, PREG_UNMATCHED_AS_NULL)) {
            if (isset($m['mask'])) {
                $start = strtr($m['mask'], ['?' => '0']);
                $end = strtr($m['mask'], ['?' => 'F']);
            } else {
                $start = $m['start'];
                $end = $m['end'] ?? $start;
            }
            return new UnicodeRange(hexdec($start), hexdec($end));
        }
        throw new ParseError("Invalid unicode range: {$text}");
    }

    private function concatenateTokens(): string
    {
        $token = $this->tokenStream->expect(TokenTypes::IDENT);
        if (strcasecmp($token->representation, 'u') !== 0) {
            throw $this->tokenStream->unexpectedValue($token->value, 'an "U" identifier');
        }
        $text = 'U';
        $token = $this->tokenStream->eatOneOf(TokenTypes::DELIM, TokenTypes::DIMENSION, TokenTypes::NUMBER);
        if ($token->type === TokenTypes::DELIM && $token->representation === '+') {
            $text .= '+';
            $token = $this->tokenStream->consume();
            $this->tokenStream->expectOneOf(TokenTypes::IDENT, TokenTypes::DELIM);
            if ($token->type === TokenTypes::IDENT) {
                $text .= $token->representation;
                $token = $this->tokenStream->consume();
            } elseif ($token->type === TokenTypes::DELIM && $token->representation === '+') {
                $text .= '+';
                $token = $this->tokenStream->consume();
            } else {
                throw $this->tokenStream->unexpectedValue($token->representation, 'an identifier or "+"');
            }
            while ($token->type === TokenTypes::DELIM && $token->representation === '?') {
                $text .= '?';
                $token = $this->tokenStream->consume();
            }
            return $text;
        }
        if ($token->type === TokenTypes::DIMENSION) {
            $text .= $token->representation;
            $token = $this->tokenStream->consume();
            while ($token->type === TokenTypes::DELIM && $token->representation === '?') {
                $text .= '?';
                $token = $this->tokenStream->consume();
            }
            return $text;
        }
        if ($token->type === TokenTypes::NUMBER) {
            $text .= $token->representation;
            $token = $this->tokenStream->consume();
            $this->tokenStream->expectOneOf(TokenTypes::DELIM, TokenTypes::DIMENSION, TokenTypes::NUMBER);
            if ($token->type === TokenTypes::DELIM && $token->representation === '?') {
                do {
                    $text .= '?';
                    $token = $this->tokenStream->consume();
                } while ($token->type === TokenTypes::DELIM && $token->representation === '?');
                return $text;
            }
            if ($token->type === TokenTypes::DIMENSION || $token->type === TokenTypes::NUMBER) {
                $text .= $token->representation;
                $this->tokenStream->consume();
                return $text;
            }
            throw $this->tokenStream->unexpectedValue($token->representation, 'a dimension, number or "?"');
        }

        return $text;
    }
}
