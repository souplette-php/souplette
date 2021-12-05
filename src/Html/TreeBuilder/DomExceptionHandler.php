<?php declare(strict_types=1);

namespace Souplette\Html\TreeBuilder;

use Souplette\Dom\Attr;
use Souplette\Dom\Document;
use Souplette\Dom\Element;
use Souplette\Dom\Exception\DomException;
use Souplette\Dom\Exception\ErrorCodes;
use Souplette\Html\Tokenizer\Token\Tag;
use Souplette\Xml\XmlNameEscaper;

final class DomExceptionHandler
{
    public static function handleCreateElementException(
        DomException $err,
        Tag $token,
        string $namespace,
        Document $doc
    ): ?Element {
        $errorCode = $err->getCode();
        if ($errorCode === ErrorCodes::INVALID_CHARACTER_ERROR || $errorCode === ErrorCodes::NAMESPACE_ERROR) {
            $token->name = XmlNameEscaper::escape($token->name);
            return $doc->createElementNS($namespace, $token->name);
        } else {
            throw new \LogicException("Unknown DomException error code: {$errorCode}", 0, $err);
        }
    }

    public static function handleCreateAttributeException(DomException $err, Document $doc, string $name, ?string $namespace = null): ?Attr
    {
        $errorCode = $err->getCode();
        if ($errorCode === ErrorCodes::INVALID_CHARACTER_ERROR || $errorCode === ErrorCodes::NAMESPACE_ERROR) {
            $name = XmlNameEscaper::escape($name);
            if ($namespace) {
                return $doc->createAttributeNS($namespace, $name);
            }
            return $doc->createAttribute($name);
        } else {
            throw new \LogicException("Unknown DomException error code: {$errorCode}", 0, $err);
        }
    }
}
