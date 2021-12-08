<?php declare(strict_types=1);

namespace Souplette\Xml\Exception;

class ParseError extends \RuntimeException
{
    public static function unsupportedNodeType(int $type): self
    {
        $name = match ($type) {
            \XMLReader::ENTITY => '#entity',
            \XMLReader::ENTITY_REF => '#entity-reference',
            \XMLReader::NOTATION => '#notation',
            default => '#unknown',
        };
        return new self(sprintf(
            'Node of type "%s" is not supported.',
            $name,
        ));
    }

    public static function fromLibXML(\libXMLError $err): self
    {
        $level = match ($err->level) {
            \LIBXML_ERR_WARNING => 'Warning',
            \LIBXML_ERR_ERROR => 'Error',
            \LIBXML_ERR_FATAL => 'Fatal error',
        };
        $message = sprintf(
            '%s: %s',
            $level,
            $err->message,
        );
        return new self($message, $err->code);
    }
}
