<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Simple;

use JetBrains\PhpStorm\Pure;
use Souplette\Css\Selectors\Node\SimpleSelector;
use Souplette\Css\Selectors\Specificity;

final class AttributeSelector extends SimpleSelector
{
    const OPERATOR_EQUALS = '=';
    const OPERATOR_INCLUDES = '~=';
    const OPERATOR_DASH_MATCH = '|=';
    const OPERATOR_PREFIX_MATCH = '^=';
    const OPERATOR_SUFFIX_MATCH = '$=';
    const OPERATOR_SUBSTRING_MATCH = '*=';

    const CASE_FORCE_INSENSITIVE = 'i';
    const CASE_FORCE_SENSITIVE = 's';

    #[Pure]
    public static function exists(string $attribute, ?string $namespace = null): self
    {
        return new self($attribute, $namespace);
    }

    #[Pure]
    public static function equals(string $attribute, string $value, ?string $namespace = null, ?string $forceCase = null): self
    {
        return new self($attribute, $namespace, self::OPERATOR_EQUALS, $value, $forceCase);
    }

    #[Pure]
    public static function includes(string $attribute, string $value, ?string $namespace = null, ?string $forceCase = null): self
    {
        return new self($attribute, $namespace, self::OPERATOR_INCLUDES, $value, $forceCase);
    }

    #[Pure]
    public static function dashMatch(string $attribute, string $value, ?string $namespace = null, ?string $forceCase = null): self
    {
        return new self($attribute, $namespace, self::OPERATOR_DASH_MATCH, $value, $forceCase);
    }

    #[Pure]
    public static function prefixMatch(string $attribute, string $value, ?string $namespace = null, ?string $forceCase = null): self
    {
        return new self($attribute, $namespace, self::OPERATOR_PREFIX_MATCH, $value, $forceCase);
    }

    #[Pure]
    public static function suffixMatch(string $attribute, string $value, ?string $namespace = null, ?string $forceCase = null): self
    {
        return new self($attribute, $namespace, self::OPERATOR_SUFFIX_MATCH, $value, $forceCase);
    }

    #[Pure]
    public static function substring(string $attribute, string $value, ?string $namespace = null, ?string $forceCase = null): self
    {
        return new self($attribute, $namespace, self::OPERATOR_SUBSTRING_MATCH, $value, $forceCase);
    }

    public function __construct(
        public string $attribute,
        public ?string $namespace = null,
        public ?string $operator = null,
        public ?string $value = null,
        public ?string $forceCase = null
    )
    {
    }

    public function __toString(): string
    {
        $qname = $this->namespace ? "{$this->namespace}|{$this->attribute}" : $this->attribute;
        if (!$this->operator) {
            return "[{$qname}]";
        }
        return sprintf(
            '[%s%s"%s"%s]',
            $qname,
            $this->operator,
            $this->value,
            $this->forceCase ? " {$this->forceCase}" : ''
        );
    }

    public function getSpecificity(): Specificity
    {
        return new Specificity(0, 1);
    }
}
