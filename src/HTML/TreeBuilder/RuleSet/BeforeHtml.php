<?php declare(strict_types=1);

namespace Souplette\HTML\TreeBuilder\RuleSet;

use Souplette\DOM\Namespaces;
use Souplette\HTML\Tokenizer\Token;
use Souplette\HTML\Tokenizer\TokenKind;
use Souplette\HTML\TreeBuilder;
use Souplette\HTML\TreeBuilder\InsertionLocation;
use Souplette\HTML\TreeBuilder\InsertionModes;
use Souplette\HTML\TreeBuilder\RuleSet;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#the-before-html-insertion-mode
 */
final class BeforeHtml extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree): void
    {
        $type = $token::KIND;
        if ($type === TokenKind::Doctype) {
            // TODO: Parse error. Ignore the token.
            return;
        } else if ($type === TokenKind::Comment) {
            $tree->insertComment($token, new InsertionLocation($tree->document));
            return;
        } else if ($type === TokenKind::Characters) {
            if (!$token->removeLeadingWhitespace()) {
                // Ignore the token.
                return;
            }
            goto ANYTHING_ELSE;
        } else if ($type === TokenKind::StartTag && $token->name === 'html') {
            // Create an element for the token in the HTML namespace, with the Document as the intended parent.
            $element = $tree->createElement($token, Namespaces::HTML, $tree->document);
            // Append it to the Document object.
            $tree->document->appendChild($element);
            // Put this element in the stack of open elements.
            $tree->openElements->push($element);
            // Switch the insertion mode to "before head".
            $tree->insertionMode = InsertionModes::BEFORE_HEAD;
            return;
        } else if ($type === TokenKind::EndTag) {
            if ($token->name === 'head' || $token->name === 'body' || $token->name === 'html' || $token->name === 'br') {
                // Act as described in the "anything else" entry below.
                goto ANYTHING_ELSE;
            } else {
                // TODO: Parse error. Ignore the token.
                return;
            }
        }
        ANYTHING_ELSE:
        // Create an html element whose node document is the Document object.
        $html = $tree->createElement(new Token\StartTag('html'), Namespaces::HTML, $tree->document);
        // Append it to the Document object.
        $tree->document->appendChild($html);
        // Put this element in the stack of open elements.
        $tree->openElements->push($html);
        // Switch the insertion mode to "before head", then reprocess the token.
        $tree->insertionMode = InsertionModes::BEFORE_HEAD;
        $tree->processToken($token);
    }
}
