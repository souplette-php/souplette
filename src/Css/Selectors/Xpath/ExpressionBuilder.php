<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath;

final class ExpressionBuilder
{
    private array $expressions = [];
    private ?Expression $current = null;

    public function element(string $axis, string $localName, ?string $namespace = null): self
    {
        $this->current = new Expression($this->current, $axis, $localName, $namespace);
        return $this;
    }

    public function axis(string $axis): self
    {
        $this->current->axis = $axis;
        return $this;
    }

    public function predicate(string $predicate): self
    {
        $this->current->addPredicate($predicate);
        return $this;
    }

    public function end(): self
    {
        if ($this->current) {
            $this->expressions[] = $this->current;
            $this->current = null;
        }
        return $this;
    }

    public function build(): string
    {
        $this->end();
        return implode(' | ', $this->expressions);
    }

    public function getLocalName(): ?string
    {
        return $this->current?->localName;
    }
}
