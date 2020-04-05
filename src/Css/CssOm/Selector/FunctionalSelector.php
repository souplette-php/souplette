<?php declare(strict_types=1);

namespace JoliPotage\Css\CssOm\Selector;

final class FunctionalSelector extends Selector
{
    private string $name;
    private array $arguments = [];

    public function __construct(string $name, array $arguments = [])
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }
}
