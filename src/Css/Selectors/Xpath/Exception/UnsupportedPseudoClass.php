<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Exception;

use Souplette\Css\Selectors\Node\PseudoClassSelector;

final class UnsupportedPseudoClass extends TranslationException
{
    public function __construct(PseudoClassSelector $selector)
    {
        parent::__construct(sprintf(
            'Unsupported pseudo-class: %s (%s)',
            $selector,
            get_debug_type($selector),
        ));
    }
}
