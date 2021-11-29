<?php declare(strict_types=1);

namespace Souplette\Css\Syntax\TokenStream;

use Souplette\Css\Syntax\Tokenizer\Token;
use Souplette\Css\Syntax\Tokenizer\TokenType;

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
        if (!$tokens || end($tokens)::TYPE !== TokenType::EOF) {
            $tokens[] = new Token\EOF(-1);
        }
        $this->tokens = $tokens;
        $this->lastIndex = (int)array_key_last($tokens);
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
