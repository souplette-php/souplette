<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Exception;

use Souplette\Css\Selectors\Node\Selector;

final class UnsupportedSelector extends TranslationException
{
    public function __construct(Selector $selector)
    {
        parent::__construct(sprintf(
            'Cannot translate selector: %s (%s)',
            $selector,
            get_debug_type($selector),
        ));
    }
}
