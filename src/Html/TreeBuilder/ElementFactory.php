<?php declare(strict_types=1);

namespace Souplette\Html\TreeBuilder;

use Souplette\Dom\Attr;
use Souplette\Dom\Element;
use Souplette\Dom\Internal\BaseNode;
use Souplette\Dom\Node;
use Souplette\Html\Tokenizer\Token\StartTag;
use Souplette\Html\Tokenizer\Token\Tag;

final class ElementFactory extends BaseNode
{
    public static function forToken(
        Tag $token,
        string $namespace,
        Node $intendedParent,
        bool $inForeignContent = false
    ): Element {
        // 3. Let document be intended parent's node document.
        $doc = match ($intendedParent->nodeType) {
            Node::DOCUMENT_NODE => $intendedParent,
            default => $intendedParent->ownerDocument,
        };
        // 4. Let local name be the tag name of the token.
        $localName = $token->name;
        // 9. Let element be the result of creating an element given document, localName, given namespace, null, and is.
        // If will execute script is true, set the synchronous custom elements flag; otherwise, leave it unset.
        $element = new Element($localName, $namespace);
        $element->document = $doc;
        // 10. Append each attribute in the given token to element.
        if ($token->attributes) {
            foreach ($token->attributes as $name => $value) {
                if ($value instanceof Attr) {
                    $element->attributes[] = $value;
                    $value->parent = $element;
                } else {
                    $attr = new Attr((string)$name);
                    $attr->value = $value;
                    $attr->document = $doc;
                    $attr->parent = $element;
                    $element->attributes[] = $attr;
                }
            }
        }

        return $element;
    }

    public static function mergeAttributes(StartTag $fromToken, Element $toElement): void
    {
        // For each attribute on the token, check to see if the attribute is already present on the element.
        // If it is not, add the attribute and its corresponding value to that element.
        foreach ($toElement->attributes as $attr) {
            unset($fromToken->attributes[$attr->localName]);
        }
        foreach ($fromToken->attributes as $name => $value) {
            $attr = new Attr((string)$name);
            $attr->value = $value;
            $attr->document = $toElement->document;
            $attr->parent = $toElement;
            $toElement->attributes[] = $attr;
        }
    }
}
