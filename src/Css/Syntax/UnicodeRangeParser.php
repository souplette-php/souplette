<?php declare(strict_types=1);

namespace Souplette\Css\Syntax;

use Souplette\Css\Syntax\Exception\ParseError;
use Souplette\Css\Syntax\Exception\UnexpectedValue;
use Souplette\Css\Syntax\Node\UnicodeRange;
use Souplette\Css\Syntax\Tokenizer\TokenTypes;
use Souplette\Css\Syntax\TokenStream\TokenStreamInterface;

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

    public function parse(array $endTokenTypes = [TokenTypes::EOF]): UnicodeRange
    {
        $text = $this->concatenateTokens();
        if ($endTokenTypes) {
            $this->tokenStream->expectOneOf(...$endTokenTypes);
        }
        if (preg_match(self::URANGE_PATTERN, $text, $m, PREG_UNMATCHED_AS_NULL)) {
            if (isset($m['mask'])) {
                $start = strtr($m['mask'], ['?' => '0']);
                $end = strtr($m['mask'], ['?' => 'F']);
            } else {
                $start = $m['start'];
                $end = $m['end'] ?? $start;
            }
            try {
                return new UnicodeRange(hexdec($start), hexdec($end));
            } catch (\Exception $err) {
                throw new ParseError("Invalid unicode range: {$text}", 0, $err);
            }
        }
        throw new ParseError("Invalid unicode range: {$text}");
    }

    private function concatenateTokens(): string
    {
        $token = $this->tokenStream->expect(TokenTypes::IDENT);
        if (strcasecmp($token->representation, 'u') !== 0) {
            throw UnexpectedValue::expecting($token->value, 'an "U" identifier');
        }
        $text = 'U';
        $this->tokenStream->consume();
        $token = $this->tokenStream->expectOneOf(TokenTypes::DELIM, TokenTypes::DIMENSION, TokenTypes::NUMBER);
        if ($token::TYPE === TokenTypes::DELIM && $token->representation === '+') {
            $text .= '+';
            $token = $this->tokenStream->consume();
            $this->tokenStream->expectOneOf(TokenTypes::IDENT, TokenTypes::DELIM);
            if ($token::TYPE === TokenTypes::IDENT) {
                $text .= $token->representation;
                $token = $this->tokenStream->consume();
            } elseif ($token::TYPE === TokenTypes::DELIM && $token->representation === '?') {
                $text .= '?';
                $token = $this->tokenStream->consume();
            } else {
                throw UnexpectedValue::expecting($token->representation, 'an identifier or "?"');
            }
            while ($token::TYPE === TokenTypes::DELIM && $token->representation === '?') {
                $text .= '?';
                $token = $this->tokenStream->consume();
            }
            return $text;
        }
        if ($token::TYPE === TokenTypes::DIMENSION) {
            $text .= $token->representation;
            $token = $this->tokenStream->consume();
            while ($token::TYPE === TokenTypes::DELIM && $token->representation === '?') {
                $text .= '?';
                $token = $this->tokenStream->consume();
            }
            return $text;
        }
        if ($token::TYPE === TokenTypes::NUMBER) {
            $text .= $token->representation;
            $token = $this->tokenStream->consume();
            if ($token::TYPE === TokenTypes::DELIM && $token->representation === '?') {
                do {
                    $text .= '?';
                    $token = $this->tokenStream->consume();
                } while ($token::TYPE === TokenTypes::DELIM && $token->representation === '?');
                return $text;
            }
            if ($token::TYPE === TokenTypes::DIMENSION || $token::TYPE === TokenTypes::NUMBER) {
                $text .= $token->representation;
                $this->tokenStream->consume();
                return $text;
            }
        }

        return $text;
    }
}
