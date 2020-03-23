<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder;

use ju1ius\HtmlParser\Tokenizer\Token\Tag;

final class DomExceptionHandler
{
    const INVALID_CHARACTER = 5;
    const NAMESPACE_ERROR = 14;

    public static function handleCreateElementException(
        \DOMException $err,
        Tag $token,
        string $namespace,
        \DOMDocument $doc
    ): ?\DOMElement {
        $errorCode = $err->getCode();
        if ($errorCode === self::INVALID_CHARACTER || $errorCode === self::NAMESPACE_ERROR) {
            $token->name = XmlUtils::escapeXmlName($token->name);
            return $doc->createElementNS($namespace, $token->name);
        } else {
            throw new \LogicException("Unknown DOMException error code: {$errorCode}", 0, $err);
        }
    }

    public static function handleSetAttributeException(\DOMException $err, \DOMElement $element, string $name, $value)
    {
        $errorCode = $err->getCode();
        if ($errorCode === self::INVALID_CHARACTER || $errorCode === self::NAMESPACE_ERROR) {
            $name = XmlUtils::escapeXmlName($name);
            return $element->setAttribute($name, $value);
        } else {
            throw new \LogicException("Unknown DOMException error code: {$errorCode}", 0, $err);
        }
    }

    public static function handleCreateAttributeException(\DOMException $err, \DOMDocument $doc, string $name, ?string $namespace = null): ?\DOMAttr
    {
        $errorCode = $err->getCode();
        if ($errorCode === self::INVALID_CHARACTER || $errorCode === self::NAMESPACE_ERROR) {
            $name = XmlUtils::escapeXmlName($name);
            if ($namespace) {
                return $doc->createAttributeNS($namespace, $name);
            }
            return $doc->createAttribute($name);
        } else {
            throw new \LogicException("Unknown DOMException error code: {$errorCode}", 0, $err);
        }
    }
}
