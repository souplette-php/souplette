<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Parser;

use ju1ius\HtmlParser\Parser\Entities\EntitySearch;
use SplQueue;
use SplStack;

abstract class AbstractTokenizer
{
    /**
     * @var int
     */
    public $state;
    /**
     * @var int
     */
    protected $returnState;
    /**
     * @var string
     */
    protected $input;
    /**
     * @var int
     */
    protected $position;
    /**
     * @var SplQueue
     */
    protected $tokenQueue;
    /**
     * @var Token
     */
    protected $currentToken;
    /**
     * @var SplStack
     */
    protected $openElements;
    /**
     * @var string
     */
    protected $appropriateEndTag;
    /**
     * @var string
     */
    protected $temporaryBuffer;
    /**
     * @var EntitySearch
     */
    protected $entitySearch;
    /**
     * @var int
     */
    protected $characterReferenceCode;

    public function __construct(string $input)
    {
        $this->input = $input;
        $this->entitySearch = EntitySearch::create();
    }

    abstract public function nextToken();

    final public function tokenize()
    {
        $this->reset();
        do {
            $carryOn = $this->nextToken();
            while (!$this->tokenQueue->isEmpty()) {
                yield $this->tokenQueue->dequeue();
            }
        } while ($carryOn);
    }

    private function reset(): void
    {
        $this->position = 0;
        $this->temporaryBuffer = '';
        $this->tokenQueue = new SplQueue();
        $this->openElements = new SplStack();
        $this->state = TokenizerStates::DATA;
    }

    protected function charsUntil(string $bytes, ?int $max = null): ?string
    {
        if ($this->position >= strlen($this->input)) {
            return null;
        }
        if ($max === 0 || $max) {
            $length = strcspn($this->input, $bytes, $this->position, $max);
        } else {
            $length = strcspn($this->input, $bytes, $this->position);
        }

        $chars = substr($this->input, $this->position, $length);
        $this->position += $length;

        return $chars;
    }

    protected function charsWhile(string $bytes, ?int $max = null): ?string
    {
        if ($this->position >= strlen($this->input)) {
            return null;
        }

        if ($max === 0 || $max) {
            $length = strspn($this->input, $bytes, $this->position, $max);
        } else {
            $length = strspn($this->input, $bytes, $this->position);
        }

        $chars = substr($this->input, $this->position, $length);
        $this->position += $length;

        return $chars;
    }

    protected function consumeWhitespace(): ?int
    {
        if ($this->position >= strlen($this->input)) {
            return null;
        }

        $length = strspn($this->input, Characters::WHITESPACE, $this->position);
        $this->position += $length;

        return $length;
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#tokenization
     */
    protected function emitCurrentToken(): void
    {
        $token = $this->currentToken;
        if ($token->type === TokenTypes::START_TAG) {
            $this->appropriateEndTag = $token->value;
        } elseif ($token->type === TokenTypes::END_TAG) {
            if ($token->attributes) {
                // When an end tag token is emitted with attributes, that is an end-tag-with-attributes parse error.
                $token->attributes = null;
            }
            if ($token->selfClosing) {
                // When an end tag token is emitted with its self-closing flag set, that is an end-tag-with-trailing-solidus parse error.
                $token->selfClosing = false;
            }
        }
        $this->tokenQueue->enqueue($token);
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#flush-code-points-consumed-as-a-character-reference
     */
    protected function flushCodePointsConsumedAsACharacterReference(): void
    {
        // https://html.spec.whatwg.org/multipage/parsing.html#charref-in-attribute
        $rs = $this->returnState;
        $isForAttribute = $rs === TokenizerStates::ATTRIBUTE_VALUE_DOUBLE_QUOTED
            || $rs === TokenizerStates::ATTRIBUTE_VALUE_SINGLE_QUOTED
            || $rs === TokenizerStates::ATTRIBUTE_VALUE_UNQUOTED;
        if ($isForAttribute) {
            $this->currentToken->attributes[count($this->currentToken->attributes) - 1][1] .= $this->temporaryBuffer;
            return;
        }
        $this->tokenQueue->enqueue(new Token(TokenTypes::CHARACTER, $this->temporaryBuffer));
    }
}
