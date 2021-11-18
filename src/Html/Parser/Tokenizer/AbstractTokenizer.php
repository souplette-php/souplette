<?php declare(strict_types=1);

namespace Souplette\Html\Parser\Tokenizer;

use Souplette\Html\Parser\Tokenizer\Token\Character;
use Souplette\Html\Parser\Tokenizer\Token\EndTag;
use Souplette\Html\Parser\Tokenizer\Token\EOF;
use Souplette\Html\Parser\Tokenizer\Token\StartTag;
use SplQueue;

abstract class AbstractTokenizer
{
    public TokenizerState $state = TokenizerState::DATA;
    public bool $allowCdata = false;
    protected int $position = 0;
    protected TokenizerState $returnState = TokenizerState::DATA;
    protected SplQueue $tokenQueue;
    protected Token $currentToken;
    protected array $parseErrors = [];
    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#appropriate-end-tag-token
     */
    protected ?string $appropriateEndTag = null;
    protected string $temporaryBuffer = '';
    protected EntitySearch $entitySearch;
    protected int $characterReferenceCode;

    public function __construct(protected string $input)
    {
        $this->entitySearch = EntitySearch::create();
    }

    final public function getPosition(): int
    {
        return $this->position;
    }

    final public function getErrors(): array
    {
        return $this->parseErrors;
    }

    abstract public function nextToken(): bool;

    final public function tokenize(TokenizerState $startState = TokenizerState::DATA, ?string $appropriateEndTag = null)
    {
        $this->reset();
        $this->state = $startState;
        $this->appropriateEndTag = $appropriateEndTag;
        do {
            $carryOn = $this->nextToken();
            while (!$this->tokenQueue->isEmpty()) {
                yield $this->tokenQueue->dequeue();
            }
        } while ($carryOn);
        yield new EOF();
    }

    private function reset(): void
    {
        $this->position = 0;
        $this->temporaryBuffer = '';
        $this->tokenQueue = new SplQueue();
        $this->parseErrors = [];
        $this->allowCdata = false;
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#tokenization
     */
    final protected function emitCurrentToken(): void
    {
        $token = $this->currentToken;
        if ($token::TYPE === TokenTypes::START_TAG) {
            /** @var StartTag $token */
            $this->appropriateEndTag = $token->name;
            if ($token->attributes) {
                $attrs = [];
                foreach ($token->attributes as [$name, $value]) {
                    if (isset($attrs[$name])) {
                        $this->parseErrors[] = [ParseErrors::DUPLICATE_ATTRIBUTE, $this->position];
                        continue;
                    }
                    $attrs[$name] = $value;
                }
                $token->attributes = $attrs;
            }
        } elseif ($token::TYPE === TokenTypes::END_TAG) {
            /** @var EndTag $token */
            if ($token->attributes) {
                // This is an end-tag-with-attributes parse error.
                $this->parseErrors[] = [ParseErrors::END_TAG_WITH_ATTRIBUTES, $this->position];
                $token->attributes = null;
            }
            if ($token->selfClosing) {
                // This is an end-tag-with-trailing-solidus parse error.
                $this->parseErrors[] = [ParseErrors::END_TAG_WITH_TRAILING_SOLIDUS, $this->position];
                $token->selfClosing = false;
            }
        }
        $this->tokenQueue->enqueue($token);
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#flush-code-points-consumed-as-a-character-reference
     */
    final protected function flushCodePointsConsumedAsACharacterReference(): void
    {
        // https://html.spec.whatwg.org/multipage/parsing.html#charref-in-attribute
        $rs = $this->returnState;
        $isForAttribute = $rs === TokenizerState::ATTRIBUTE_VALUE_DOUBLE_QUOTED
            || $rs === TokenizerState::ATTRIBUTE_VALUE_SINGLE_QUOTED
            || $rs === TokenizerState::ATTRIBUTE_VALUE_UNQUOTED;
        if ($isForAttribute) {
            $this->currentToken->attributes[count($this->currentToken->attributes) - 1][1] .= $this->temporaryBuffer;
            return;
        }
        $this->tokenQueue->enqueue(new Character($this->temporaryBuffer));
    }

    /**
     * Consumes characters until a character in `$bytes` is seen.
     *
     * Usages of this method have been inlined into the generated tokenizer via a twig macro.
     *
     * @param string $bytes
     * @return string
     * @codeCoverageIgnore
     */
    final protected function charsUntil(string $bytes): string
    {
        $length = strcspn($this->input, $bytes, $this->position);
        $chars = substr($this->input, $this->position, $length);
        $this->position += $length;

        return $chars;
    }

    /**
     * Consumes characters while the input matches a character in `$bytes`.
     *
     * Usages of this method have been inlined into the generated tokenizer via a twig macro.
     *
     * @param string $bytes
     * @return string
     * @codeCoverageIgnore
     */
    final protected function charsWhile(string $bytes): string
    {
        $length = strspn($this->input, $bytes, $this->position);
        $chars = substr($this->input, $this->position, $length);
        $this->position += $length;

        return $chars;
    }
}
