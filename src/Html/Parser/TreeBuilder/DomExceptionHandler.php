<?php declare(strict_types=1);

namespace JoliPotage\Html\Parser\TreeBuilder;

use JoliPotage\Html\Dom\ErrorCodes;
use JoliPotage\Html\Parser\Tokenizer\Token\Tag;
use JoliPotage\Xml\XmlNameEscaper;

final class DomExceptionHandler
{
    public static function handleCreateElementException(
        \DOMException $err,
        Tag $token,
        string $namespace,
        \DOMDocument $doc
    ): ?\DOMElement {
        $errorCode = $err->getCode();
        if ($errorCode === ErrorCodes::INVALID_CHARACTER_ERROR || $errorCode === ErrorCodes::NAMESPACE_ERROR) {
            $token->name = XmlNameEscaper::escape($token->name);
            return $doc->createElementNS($namespace, $token->name);
        } else {
            throw new \LogicException("Unknown DOMException error code: {$errorCode}", 0, $err);
        }
    }

    public static function handleCreateAttributeException(\DOMException $err, \DOMDocument $doc, string $name, ?string $namespace = null): ?\DOMAttr
    {
        $errorCode = $err->getCode();
        if ($errorCode === ErrorCodes::INVALID_CHARACTER_ERROR || $errorCode === ErrorCodes::NAMESPACE_ERROR) {
            $name = XmlNameEscaper::escape($name);
            if ($namespace) {
                return $doc->createAttributeNS($namespace, $name);
            }
            return $doc->createAttribute($name);
        } else {
            throw new \LogicException("Unknown DOMException error code: {$errorCode}", 0, $err);
        }
    }
}
