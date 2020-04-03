<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom;

/**
 * @see https://dom.spec.whatwg.org/#interface-domtokenlist
 *
 * @property string $value
 * @property-read int $length
 */
final class TokenList implements \Countable, \IteratorAggregate
{
    /**
     * @var string[]
     */
    private array $tokens = [];
    /**
     * @var int[]
     */
    private array $indices = [];
    private \Closure $synchronize;

    public function __construct(string $value, \Closure $synchronize)
    {
        $this->setValue($value);
        $this->synchronize = $synchronize;
    }

    public function __get($name)
    {
        if ($name === 'value') {
            return $this->getValue();
        } elseif ($name === 'length') {
            return $this->count();
        }
    }

    public function __set($name, $value)
    {
        if ($name === 'value') {
            $this->setValue($value);
        }
    }

    public function setValue(string $value): void
    {
        $this->indices = [];
        $this->tokens = preg_split('/\s+/', $value, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($this->tokens as $i => $token) {
            $this->indices[$token] = $i;
        }
    }

    public function getValue(): string
    {
        return implode(' ', $this->tokens);
    }

    public function contains(string $token): bool
    {
        return isset($this->indices[$token]);
    }

    public function add(string ...$tokens): void
    {
        $index = array_key_last($this->tokens) ?? -1;
        foreach ($tokens as $token) {
            if ($this->contains($token)) {
                continue;
            }
            $index++;
            $this->tokens[$index] = $token;
            $this->indices[$token] = $index;
        }
        ($this->synchronize)($this->getValue());
    }

    public function remove(string ...$tokens): void
    {
        foreach ($tokens as $token) {
            if (!$this->contains($token)) {
                continue;
            }
            $index = $this->indices[$token];
            unset($this->indices[$token]);
            unset($this->tokens[$index]);
        }
        ($this->synchronize)($this->getValue());
    }

    public function replace(string $old, string $new): void
    {
        if (!$this->contains($old)) {
            return;
        }
        $index = $this->indices[$old];
        unset($this->indices[$old]);
        $this->indices[$new] = $index;
        $this->tokens[$index] = $new;
        ($this->synchronize)($this->getValue());
    }

    public function toggle(string $token, ?bool $force = null): bool
    {
        if ($force === null) {
            if ($this->contains($token)) {
                $this->remove($token);
                return false;
            }
            $this->add($token);
            return true;
        }
        if ($force) {
            $this->add($token);
            return true;
        }

        $this->remove($token);
        return false;
    }

    public function count()
    {
        return count($this->tokens);
    }

    public function getIterator()
    {
        return new \ArrayIterator(array_values($this->tokens));
    }

    public function __toString()
    {
        return $this->getValue();
    }
}
