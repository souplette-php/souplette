<?php declare(strict_types=1);

namespace Souplette\DOM\Internal;

use Souplette\DOM\Document;
use Souplette\DOM\DocumentFragment;
use Souplette\DOM\Exception\DOMException;
use Souplette\DOM\Node;
use Souplette\DOM\Text;

/**
 * @internal
 */
final class Idioms
{
    const ASCII_WHITESPACE = " \n\t\r\f";

    public static function splitInputOnAsciiWhitespace(string $input): iterable
    {
        $token = strtok($input, self::ASCII_WHITESPACE);
        $i = 0;
        while ($token) {
            yield $i++ => $token;
            $token = strtok(self::ASCII_WHITESPACE);
        }
    }

    public static function stripAndCollapseAsciiWhitespace(string $input): string
    {
        return trim(preg_replace('/\s+/', ' ', $input));
    }

    /**
     * @throws DOMException
     */
    public static function convertNodesIntoNode(Document $doc, array $nodes): Node
    {
        // 1. Let node be null.
        // 2. Replace each string in nodes with a new Text node whose data is the string and node document is document.
        foreach ($nodes as $i => $node) {
            if (\is_string($node)) {
                $node = new Text($node);
                $node->_doc = $doc;
                $nodes[$i] = $node;
            }
        }
        // 3. If nodes contains one node, then set node to nodes[0].
        if (\count($nodes) === 1) {
            return $nodes[0];
        }
        // 4. Otherwise, set node to a new DocumentFragment node whose node document is document,
        // and then append each node in nodes, if any, to it.
        $frag = new DocumentFragment();
        $frag->_doc = $doc;
        foreach ($nodes as $node) {
            $frag->insertBefore($node);
        }

        return $frag;
    }
}
