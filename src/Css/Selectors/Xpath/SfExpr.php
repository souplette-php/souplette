<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath;

use JetBrains\PhpStorm\Pure;

final class SfExpr
{
    public function __construct(
        private string $path = '',
        private string $element = '*',
        private string $condition = '',
        bool $starPrefix = false
    )
    {
        if ($starPrefix) {
            $this->addStarPrefix();
        }
    }

    public function getElement(): string
    {
        return $this->element;
    }

    public function addCondition(string $condition): self
    {
        $this->condition = $this->condition ? sprintf('(%s) and (%s)', $this->condition, $condition) : $condition;

        return $this;
    }

    public function getCondition(): string
    {
        return $this->condition;
    }

    public function setElement(string $localName, ?string $namespace = null)
    {
        if (!$namespace || $namespace === '*') {
            $this->element = '*';
            $this->addCondition(sprintf('local-name() = %s', Utils::getXpathLiteral($localName)));
        } else {
            $this->element = "{$namespace}:{$localName}";
        }
    }

    /**
     * @return $this
     */
    public function addNameTest(): self
    {
        if ($this->element !== '*') {
            $this->addCondition('name() = ' . Utils::getXpathLiteral($this->element));
            $this->element = '*';
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function addStarPrefix(): self
    {
        $this->path .= '*/';

        return $this;
    }

    /**
     * Joins another XPathExpr with a combiner.
     *
     * @return $this
     */
    public function join(string $combiner, self $expr): self
    {
        $path = $this->__toString() . $combiner;

        if ($expr->path !== '*/') {
            $path .= $expr->path;
        }

        $this->path = $path;
        $this->element = $expr->element;
        $this->condition = $expr->condition;

        return $this;
    }

    #[Pure]
    public function __toString(): string
    {
        return sprintf(
            '%s%s%s',
            $this->path,
            $this->element,
            $this->condition ? "[{$this->condition}]" : ''
        );
    }
}
