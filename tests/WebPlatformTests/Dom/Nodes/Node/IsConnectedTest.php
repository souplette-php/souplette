<?php declare(strict_types=1);

namespace Souplette\Tests\WebPlatformTests\Dom\Nodes\Node;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Dom\Document;

/**
 * Ported from web-platform-tests:
 * wpt/dom/nodes/Node-isConnected.html
 *
 * We don't test iframes here since we don't implement frames.
 */
final class IsConnectedTest extends TestCase
{
    public function testNewNodeIsDisconnected()
    {
        $doc = new Document('html');
        $node = $doc->createElement('html');
        Assert::assertFalse($node->isConnected, 'new node is disconnected');
    }

    public function testAppendChildConnectsNode()
    {
        $doc = new Document('html');
        $node = $doc->createElement('html');
        Assert::assertFalse($node->isConnected, 'new node is disconnected');
        $doc->appendChild($node);
        Assert::assertTrue($node->isConnected, 'appendChild connects child');
    }

    public function testAppendChildConnectsAllDescendants()
    {
        $doc = new Document('html');
        $root = $doc->createElement('html');
        Assert::assertFalse($root->isConnected, 'new node is disconnected');
        $child = $root->appendChild($doc->createElement('div'));
        Assert::assertFalse($child->isConnected, 'new node is disconnected');
        $grandChild = $child->appendChild($doc->createElement('div'));
        Assert::assertFalse($grandChild->isConnected, 'new node is disconnected');
        //
        $doc->appendChild($root);
        Assert::assertTrue($root->isConnected, 'appendChild connects child');
        Assert::assertTrue($child->isConnected, 'appendChild connects grand-child');
        Assert::assertTrue($grandChild->isConnected, 'appendChild connects grand-grand-child');
    }

    public function testRemovalDisconnectsDirectChild()
    {
        $doc = new Document('html');
        $doc->appendChild($node = $doc->createElement('html'));
        Assert::assertTrue($node->isConnected, 'appendChild connects child');
        $node->remove();
        Assert::assertFalse($node->isConnected, 'direct child is disconnected after removal');
    }

    public function testRemovalDisconnectsAllDescendants()
    {
        $doc = new Document('html');
        $root = $doc->createElement('html');
        $child = $root->appendChild($doc->createElement('div'));
        $grandChild = $child->appendChild($doc->createElement('div'));
        $doc->appendChild($root);
        Assert::assertTrue($root->isConnected, 'appendChild connects child');
        Assert::assertTrue($child->isConnected, 'appendChild connects grand-child');
        Assert::assertTrue($grandChild->isConnected, 'appendChild connects grand-grand-child');
        //
        $root->remove();
        Assert::assertFalse($root->isConnected, 'removal disconnects child');
        Assert::assertFalse($child->isConnected, 'removal disconnects grand-child');
        Assert::assertFalse($grandChild->isConnected, 'removal disconnects grand-grand-child');
    }
}
