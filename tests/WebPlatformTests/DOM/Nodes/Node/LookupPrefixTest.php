<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\DOM\Nodes\Node;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Souplette\DOM\Namespaces;
use Souplette\DOM\Node;
use Souplette\Tests\DOM\DOMBuilder;

/**
 * Ported from web-platform-tests
 * wpt/dom/nodes/Node-lookupPrefix.html
 */
final class LookupPrefixTest extends TestCase
{
    #[DataProvider('lookupPrefixProvider')]
    public function testLookupPrefix(Node $node, ?string $ns, ?string $expected): void
    {
        Assert::assertSame($expected, $node->lookupPrefix($ns));
    }

    public static function lookupPrefixProvider(): iterable
    {
        $doc = DOMBuilder::xml()
            ->tag('html')->prefix('x', 'test')
                ->tag('body')->prefix('s', 'test')
                    ->tag('x')->prefix('t', 'test')
                        ->comment('comment')
                        ->pi('test', 'test')
                        ->text('TEST')
                        ->tag('x')->close()
                    ->close()
            ->getDocument();
        // TODO: add test for when there's no documentElement
        yield [$doc, 'test', 'x'];
        yield [$doc, null, null];
        $x = $doc->documentElement->firstChild->firstChild;
        yield [$x, Namespaces::HTML, null];
        yield [$x, 'something', null];
        yield [$x, null, null];
        yield [$x, 'test', 't'];
        yield [$x->parentNode, 'test', 's'];
        yield [$x->firstChild, 'test', 't'];
        yield [$x->firstChild->nextSibling, 'test', 't'];
        yield [$x->firstChild->nextSibling->nextSibling, 'test', 't'];
        yield [$x->lastChild, 'test', 't'];
    }
}
