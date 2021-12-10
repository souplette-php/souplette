<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\Dom\Nodes\Node;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\Node;
use Souplette\Dom\Traversal\NodeTraversal;
use Souplette\Tests\WebPlatformTests\Dom\CommonProvider;

final class CompareDocumentPositionTest extends TestCase
{
    /**
     * @dataProvider compareDocumentPositionProvider
     */
    public function testCompareDocumentPosition(Node $reference, Node $other, array|int $expected)
    {
        $result = $reference->compareDocumentPosition($other);
        if (\is_array($expected)) {
            Assert::assertContains($result, $expected);
        } else {
            Assert::assertSame($expected, $result);
        }
    }

    public function compareDocumentPositionProvider(): \Traversable
    {
        /** @var Node[] $nodes */
        $nodes = iterator_to_array(CommonProvider::testNodes());
        foreach ($nodes as $refKey => $reference) {
            foreach ($nodes as $otherKey => $other) {
                $key = "{$refKey}->compareDocumentPosition({$otherKey})";
                // "If other and reference are the same object, return zero and terminate these steps."
                if ($reference === $other) {
                    yield $key => [$reference, $other, 0];
                    continue;
                }
                // "If other and reference are not in the same tree,
                // return the result of adding DOCUMENT_POSITION_DISCONNECTED,
                // DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC, and either
                // DOCUMENT_POSITION_PRECEDING or DOCUMENT_POSITION_FOLLOWING,
                // with the constraint that this is to be consistent together
                // and terminate these steps."
                if ($reference->getRootNode() !== $other->getRootNode()) {
                    $connection = Node::DOCUMENT_POSITION_DISCONNECTED + Node::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC;
                    $expected = [
                        $connection + Node::DOCUMENT_POSITION_PRECEDING,
                        $connection + Node::DOCUMENT_POSITION_FOLLOWING,
                    ];
                    yield $key => [$reference, $other, $expected];
                    continue;
                }
                // "If other is an ancestor of reference,
                // return the result of adding DOCUMENT_POSITION_CONTAINS to DOCUMENT_POSITION_PRECEDING
                // and terminate these steps."
                $ancestor = $reference->parentNode;
                while ($ancestor && $ancestor !== $other) $ancestor = $ancestor->parentNode;
                if ($ancestor === $other) {
                    $expected = Node::DOCUMENT_POSITION_CONTAINS + Node::DOCUMENT_POSITION_PRECEDING;
                    yield $key => [$reference, $other, $expected];
                    continue;
                }
                // "If other is a descendant of reference,
                // return the result of adding DOCUMENT_POSITION_CONTAINED_BY to DOCUMENT_POSITION_FOLLOWING
                // and terminate these steps."
                $ancestor = $other->parentNode;
                while ($ancestor && $ancestor !== $reference) $ancestor = $ancestor->parentNode;
                if ($ancestor === $reference) {
                    $expected = Node::DOCUMENT_POSITION_CONTAINED_BY + Node::DOCUMENT_POSITION_FOLLOWING;
                    yield $key => [$reference, $other, $expected];
                    continue;
                }
                // "If other is preceding reference
                // return DOCUMENT_POSITION_PRECEDING and terminate these steps."
                $prev = NodeTraversal::previous($reference);
                while ($prev && $prev !== $other) $prev = NodeTraversal::previous($prev);
                if ($prev === $other) {
                    yield $key => [$reference, $other, Node::DOCUMENT_POSITION_PRECEDING];
                    continue;
                }
                // "Return DOCUMENT_POSITION_FOLLOWING."
                yield $key => [$reference, $other, Node::DOCUMENT_POSITION_FOLLOWING];
            }
        }
    }
}
