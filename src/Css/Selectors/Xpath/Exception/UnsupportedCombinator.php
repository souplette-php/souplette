<?php declare(strict_types=1);

namespace Souplette\Css\Selectors\Xpath\Exception;

final class UnsupportedCombinator extends TranslationException
{
    public function __construct(string $combinator)
    {
        parent::__construct(sprintf(
            'Unsupported combinator %s',
            $combinator,
        ));
    }
}
