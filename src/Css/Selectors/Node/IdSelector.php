<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node;

final class IdSelector extends SimpleSelector
{
    private string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function __toString()
    {
        return "#{$this->id}";
    }
}
