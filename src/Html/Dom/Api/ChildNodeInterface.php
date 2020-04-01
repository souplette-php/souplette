<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom\Api;

use DOMNode;

/**
 * @see https://dom.spec.whatwg.org/#interface-childnode
 */
interface ChildNodeInterface
{
    /**
     * @param DOMNode|string ...$nodes
     */
    public function before(...$nodes): void;
    /**
     * @param DOMNode|string ...$nodes
     */
    public function after(...$nodes): void;
    /**
     * @param DOMNode|string ...$nodes
     */
    public function replaceWith(...$nodes): void;
    public function remove(): void;
}
