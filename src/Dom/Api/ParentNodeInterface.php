<?php declare(strict_types=1);

namespace Souplette\Dom\Api;

use Souplette\Dom\Node\Element;
use Souplette\Dom\Node\Node;

interface ParentNodeInterface extends NodeInterface
{
    /**
     * @return Element[]
     */
    public function getChildren(): array;

    public function getFirstElementChild(): ?Element;
    public function getLastElementChild(): ?Element;
    public function getChildElementCount(): int;

    public function prepend(Node|string ...$nodes): void;
    public function append(Node|string ...$nodes): void;
    public function replaceChildren(Node|string ...$nodes): void;

    public function querySelector(string $selectors): ?Element;
    /**
     * @return Element[]
     */
    public function querySelectorAll(string $selectors): array;
}
