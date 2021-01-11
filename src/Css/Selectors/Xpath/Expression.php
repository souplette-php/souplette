<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath;

use Souplette\Css\Selectors\Xpath\Helper\StringHelper;

final class Expression
{
    /**
     * @var string[]
     */
    private array $predicates = [];

    public function __construct(
        private ?Expression $parent,
        public string $axis,
        public string $localName,
        public string $namespace = '*',
    ) {
    }

    public function addPredicate(string $predicate)
    {
        $this->predicates[] = $predicate;
    }

    public function __toString(): string
    {
        $predicates = array_values($this->predicates);
        if (!$this->namespace || $this->namespace === '*') {
            $element = '*';
            if ($this->localName !== '*') {
                $namePred = sprintf('local-name() = %s', StringHelper::toStringLiteral($this->localName));
                array_unshift($predicates, $namePred);
            }
        } else {
            $element = "{$this->namespace}:{$this->localName}";
        }

        return sprintf(
            '%s%s%s[%s]',
            $this->parent ? "{$this->parent}/" : '',
            $this->axis,
            $element,
            implode(' and ', array_map(fn($pred) => "({$pred})", $predicates)),
        );
    }
}
