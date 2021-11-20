<?php declare(strict_types=1);

namespace Souplette\Css\Syntax;

use Souplette\Css\Syntax\Exception\ParseError;
use Souplette\Css\Syntax\Node\AnPlusB;
use Souplette\Css\Syntax\Tokenizer\TokenType;
use Souplette\Css\Syntax\TokenStream\TokenStreamInterface;

final class AnPlusBStringParser
{
    private TokenStreamInterface $tokenStream;
    /**
     * @var int[]
     */
    private array $endTokenTypes;

    private const SYNTAX = <<<'REGEXP'
    /
        ^ \s*
        (?:
            (?<odd> odd )
            | (?<even> even )
            | (?<int> [+-]? \d+ )
            | (?<a_sign> [+-]? ) (?<a> \d* ) n (?: \s* (?<b_sign> [+-] ) \s* (?<b> \d+ ) )?
        )
        \s* $
    /xi
    REGEXP;

    public function __construct(TokenStreamInterface $tokenStream, array $endTokenTypes = [TokenType::EOF])
    {
        $this->tokenStream = $tokenStream;
        $this->endTokenTypes = $endTokenTypes;
    }

    public function parse(): AnPlusB
    {
        $input = $this->serializeTokenStream();
        if (preg_match(self::SYNTAX, $input, $m, PREG_UNMATCHED_AS_NULL)) {
            if (isset($m['odd'])) {
                return new AnPlusB(2, 1);
            } else if (isset($m['even'])) {
                return new AnPlusB(2, 0);
            } else if (isset($m['int'])) {
                return new AnPlusB(0, (int)$m['int']);
            } else {
                $aSign = $m['a_sign'] === '-' ? -1 : 1;
                $a = $m['a'] === '' ? 1 : (int)$m['a'];
                $bSign = $m['b_sign'] === '-' ? -1 : 1;
                $b = (int)$m['b'];
                return new AnPlusB($a * $aSign, $b * $bSign);
            }
        }
        throw new ParseError('Syntax error: invalid An+B expression.');
    }

    private function serializeTokenStream(): string
    {
        $s = '';
        do {
            $token = $this->tokenStream->current();
            $s .= $token->representation;
            $token = $this->tokenStream->consume();
        } while (!\in_array($token::TYPE, $this->endTokenTypes, true));
        return $s;
    }
}
