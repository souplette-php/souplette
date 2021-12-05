<?php declare(strict_types=1);

namespace Souplette\Dom\Api;

use Souplette\Dom\Node\Node;

/**
 * Implemented by DocumentType, Element and CharacterData
 *
 * @see https://dom.spec.whatwg.org/#interface-childnode
 */
interface ChildNodeInterface extends NodeInterface
{
    public function before(Node|string ...$nodes): void;
    public function after(Node|string ...$nodes): void;
    public function replaceWith(Node|string ...$nodes): void;
    public function remove(): void;
}
