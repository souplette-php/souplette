<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Node\Functional;

use Souplette\Css\Selectors\Node\FunctionalSelector;

/**
 * @see https://drafts.csswg.org/selectors/#the-dir-pseudo
 */
final class Dir extends FunctionalSelector
{
    public function __construct(string $direction)
    {
        parent::__construct('dir', [$direction]);
    }

    public function __toString(): string
    {
        return ":dir({$this->arguments[0]})";
    }
}
