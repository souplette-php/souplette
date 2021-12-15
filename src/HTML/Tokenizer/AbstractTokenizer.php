<?php declare(strict_types=1);

namespace Souplette\HTML\Tokenizer;

use Souplette\HTML\Tokenizer\Token\Character;
use Souplette\HTML\Tokenizer\Token\EndTag;
use Souplette\HTML\Tokenizer\Token\EOF;
use Souplette\HTML\Tokenizer\Token\StartTag;
use SplQueue;

abstract class AbstractTokenizer
{
    public TokenizerState $state = TokenizerState::DATA;
    public \Closure $allowCdata;
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
        $this->allowCdata = fn() => false;
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

    /**
     * @return iterable<Token>
     */
    final public function tokenize(TokenizerState $startState = TokenizerState::DATA, ?string $appropriateEndTag = null): iterable
    {
        $this->reset();
        $this->state = $startState;
        $this->appropriateEndTag = $appropriateEndTag;
        do {
            $carryOn = $this->nextToken();
            yield from $this->tokenQueue;
        } while ($carryOn);
        yield new EOF();
    }

    private function reset(): void
    {
        $this->position = 0;
        $this->temporaryBuffer = '';
        $this->tokenQueue = new SplQueue();
        $this->tokenQueue->setIteratorMode(\SplDoublyLinkedList::IT_MODE_DELETE|\SplDoublyLinkedList::IT_MODE_FIFO);
        $this->parseErrors = [];
    }

    /**
     * @see https://html.spec.whatwg.org/multipage/parsing.html#tokenization
     */
    final protected function emitCurrentToken(): void
    {
        $token = $this->currentToken;
        if ($token::TYPE === TokenType::START_TAG) {
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
        } else if ($token::TYPE === TokenType::END_TAG) {
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
        $isForAttribute = match($rs) {
            TokenizerState::ATTRIBUTE_VALUE_DOUBLE_QUOTED,
            TokenizerState::ATTRIBUTE_VALUE_SINGLE_QUOTED,
            TokenizerState::ATTRIBUTE_VALUE_UNQUOTED,
                => true,
            default => false,
        };
        if ($isForAttribute) {
            $this->currentToken->attributes[array_key_last($this->currentToken->attributes)][1] .= $this->temporaryBuffer;
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
