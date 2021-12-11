<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\Dom\Nodes\Node;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\Node;
use Souplette\Tests\WebPlatformTests\Dom\CommonProvider;

final class ContainsTest extends TestCase
{
    /**
     * @dataProvider containsProvider
     */
    public function testContains(Node $reference, ?Node $other, bool $expected)
    {
        Assert::assertSame($expected, $reference->contains($other));
    }

    public function containsProvider(): iterable
    {
        /** @var Node[] $nodes */
        $nodes = iterator_to_array(CommonProvider::testNodes());
        foreach ($nodes as $refKey => $reference) {
            yield "{$refKey}->contains(null)" => [$reference, null, false];
            foreach ($nodes as $otherKey => $other) {
                $key = "{$refKey}->contains({$otherKey})";
                $expected = false;
                for ($ancestor = $other; $ancestor; $ancestor = $ancestor->parentNode) {
                    if ($ancestor === $reference) $expected = true;
                }
                yield $key => [$reference, $other, $expected];
            }
        }
    }
}
