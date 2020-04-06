<?php declare(strict_types=1);

namespace JoliPotage\Css\Syntax\Node;

final class CssFunction extends CssValue
{
    public string $name;
    public array $arguments;

    public function __construct(string $name, array $arguments = [])
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }
}
