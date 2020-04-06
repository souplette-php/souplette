<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Node;

final class CssAtRule extends CssRule
{
    public string $name;
    /**
     * @var CssValue[]
     */
    public array $prelude;
    /**
     * Nullable to handle at-statements
     */
    public ?CssSimpleBlock $body;

    public function __construct(string $name, array $prelude = [], ?CssSimpleBlock $body = null)
    {
        $this->name = $name;
        $this->prelude = $prelude;
        $this->body = $body;
    }
}
