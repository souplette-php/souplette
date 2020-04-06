<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Node;

final class CssDeclaration extends CssRule
{
    public string $name;
    /**
     * @var CssValue[]
     */
    public array $body;

    public function __construct(string $name, array $body = [])
    {
        $this->name = $name;
        $this->body = $body;
    }
}
