<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\Node;

final class CSSAtRule extends CSSRule
{
    public string $name;
    /**
     * @var CSSValue[]
     */
    public array $prelude;
    /**
     * Nullable to handle at-statements
     */
    public ?CSSSimpleBlock $body;

    public function __construct(string $name, array $prelude = [], ?CSSSimpleBlock $body = null)
    {
        $this->name = $name;
        $this->prelude = $prelude;
        $this->body = $body;
    }
}
