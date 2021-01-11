<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Functional;

use Souplette\Css\Selectors\Node\FunctionalSelector;

final class Lang extends FunctionalSelector
{
    public function __construct(string ...$languages)
    {
        parent::__construct('lang', $languages);
    }

    public function __toString(): string
    {
        $languages = array_map(fn($l) => sprintf('"%s"', $l), $this->arguments);
        $languages = implode(', ', $languages);
        return ":lang({$languages})";
    }
}
