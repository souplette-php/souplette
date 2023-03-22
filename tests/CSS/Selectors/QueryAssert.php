<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors;

use PHPUnit\Framework\Assert;
use Souplette\CSS\Selectors\Node\Selector;
use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\DOM\Element;
use Souplette\Tests\Utils;

final class QueryAssert
{
    public static function elementMatchesSelector(
        Element $element,
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
