<?php declare(strict_types=1);

namespace Souplette\Dom\Internal;

final class NodeFlags
{
    const NODE_TYPE_SHIFT = 2;
    const ELEMENT_NS_SHIFT = 4;

    // Node type flags. These never change once created.
    const IS_CONTAINER = 1 << 1;
    const NODE_TYPE_MASK = 0x03 << self::NODE_TYPE_SHIFT;
    const ELEMENT_NS_MASK = 0x03 << self::ELEMENT_NS_SHIFT;

    // Tree state flags. These change when the element is added/removed from a DOM tree.
    const IS_CONNECTED = 1 << 8;
    const IN_SHADOW_TREE = 1 << 9;
}
