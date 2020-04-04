<?php declare(strict_types=1);

namespace JoliPotage\Css\Parser\TokenStream;

use JoliPotage\Css\Parser\Tokenizer\Token;
use JoliPotage\Css\Parser\Tokenizer\TokenTypes;

final class TokenRange extends AbstractTokenStream
{
    /**
     * @var Token[]
     */
    private array $tokens;
    private int $position = 0;
    private int $lastIndex;

    public function __construct(array $tokens)
    {
        if (end($tokens)->type !== TokenTypes::EOF) {
            $tokens[] = new Token\EOF(-1);
        }
        $this->tokens = $tokens;
        $this->lastIndex = count($this->tokens) - 1;
    }

    public function consume(int $n = 1): Token
    {
        $this->position = min($this->position + $n, $this->lastIndex);
        return $this->tokens[$this->position];
    }

    public function lookahead(int $offset = 1): Token
    {
        $position = min($this->position + $offset, $this->lastIndex);
        return $this->tokens[$position];
    }

    public function current(): Token
    {
        return $this->tokens[$this->position];
    }
}
