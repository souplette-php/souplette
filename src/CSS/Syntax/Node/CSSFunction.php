<?php declare(strict_types=1);

namespace Souplette\CSS\Syntax\Node;

final class CSSFunction extends CSSValue
{
    public string $name;
    public array $arguments;

    public function __construct(string $name, array $arguments = [])
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }
}
