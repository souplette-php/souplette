<?php declare(strict_types=1);

namespace Souplette\Xml;

use Souplette\Dom\Element;
use Souplette\Dom\Exception\DomException;
use Souplette\Dom\Exception\InvalidStateError;
use Souplette\Dom\Node;
use Souplette\Dom\Serialization\MarkupAccumulator;
use Souplette\Dom\Serialization\MarkupFormatter;
use Souplette\Dom\Serialization\NodeValidator;
use Souplette\Dom\Serialization\SerializationType;

/**
 * @see https://w3c.github.io/DOM-Parsing/#xml-serialization
 */
final class XmlSerializer
{
    /**
     * @throws InvalidStateError
     */
    public function serialize(Node $node, bool $requireWellFormed = false): string
    {
        try {
            return $this->createAccumulator($requireWellFormed)->serialize($node);
        } catch (DomException $err) {
            throw new InvalidStateError($err->getMessage(), $err);
        }
    }

    /**
     * @throws InvalidStateError
     */
    public function serializeFragment(Element $node, bool $requireWellFormed = false): string
    {
        try {
            return $this->createAccumulator($requireWellFormed)->serialize($node, true);
        } catch (DomException $err) {
            throw new InvalidStateError($err->getMessage(), $err);
        }
    }

    private function createAccumulator(bool $requireWellFormed): MarkupAccumulator
    {
        return new MarkupAccumulator(
            new MarkupFormatter(SerializationType::XML),
            $requireWellFormed ? new NodeValidator() : null,
        );
    }
}
