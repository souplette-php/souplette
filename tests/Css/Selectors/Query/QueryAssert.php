<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Query;

use PHPUnit\Framework\Assert;
use Souplette\Css\Selectors\Node\Selector;
use Souplette\Css\Selectors\Query\Compiler;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Tests\Utils;

final class QueryAssert
{
    public static function elementMatchesSelector(
        \DOMElement $element,
        Selector $selector,
        bool $mustMatch = true,
        string $message = ''
    ) {
        $ctx = QueryContext::of($element);
        $result = $selector->matches($ctx, $element);
        if (!$message) {
            $message = sprintf(
                'Failed asserting that selector "%s" %s element "%s"',
                $selector,
                $mustMatch ? 'matches' : 'does not match',
                Utils::elementPath($element),
            );
        }
        Assert::assertSame($mustMatch, $result, $message);
    }
}
