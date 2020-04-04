<?php declare(strict_types=1);

namespace JoliPotage\Css\Parser\TokenStream;

use JoliPotage\Css\Parser\Tokenizer\Token;
use JoliPotage\Css\Parser\Tokenizer\Tokenizer;

final class TokenStream extends AbstractTokenStream
{
    private Tokenizer $tokenizer;
    private \SplFixedArray $lookaheadBuffer;
    private int $lookaheadBufferSize;
    private int $position = 0;

    public function __construct(Tokenizer $tokenizer, int $lookaheadSize)
    {
        $this->tokenizer = $tokenizer;
        $this->lookaheadBufferSize = $lookaheadSize + 1;
        $this->lookaheadBuffer = new \SplFixedArray($this->lookaheadBufferSize);
        $this->position = 0;
        $this->fillLookaheadBuffer();
    }

    public function consume(int $n = 1): Token
    {
        for ($i = 0; $i < $n; $i++) {
            $this->lookaheadBuffer[$this->position] = $this->tokenizer->consumeToken();
            $this->position = ($this->position + 1) % $this->lookaheadBufferSize;
        }
        return $this->lookaheadBuffer[$this->position];
    }

    public function lookahead(int $offset = 1): Token
    {
        // TODO: should we throw when $offset > $lookaheadBufferSize ?
        $bufferOffset = ($this->position + $offset - 1) % $this->lookaheadBufferSize;
        return $this->lookaheadBuffer[$bufferOffset];
    }

    public function current(): Token
    {
        return $this->lookaheadBuffer[$this->position];
    }

    private function fillLookaheadBuffer(): void
    {
        for ($i = 0; $i < $this->lookaheadBufferSize; $i++) {
            $this->lookaheadBuffer[$i] = $this->tokenizer->consumeToken();
        }
    }
}
