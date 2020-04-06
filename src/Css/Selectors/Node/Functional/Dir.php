<?php declare(strict_types=1);

namespace JoliPotage\Css\Selectors\Node\Functional;

use JoliPotage\Css\Selectors\Node\FunctionalSelector;

/**
 * @see https://drafts.csswg.org/selectors/#the-dir-pseudo
 */
final class Dir extends FunctionalSelector
{
    public function __construct(string $direction)
    {
        parent::__construct('dir', [$direction]);
    }

    public function __toString()
    {
        return ":dir({$this->arguments[0]})";
    }
}
