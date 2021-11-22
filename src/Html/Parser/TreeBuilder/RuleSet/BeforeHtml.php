<?php declare(strict_types=1);

namespace Souplette\Html\Parser\TreeBuilder\RuleSet;

use Souplette\Dom\Namespaces;
use Souplette\Html\Parser\TreeBuilder\InsertionLocation;
use Souplette\Html\Parser\TreeBuilder\InsertionModes;
use Souplette\Html\Parser\TreeBuilder\RuleSet;
use Souplette\Html\Parser\TreeBuilder\TreeBuilder;
use Souplette\Html\Tokenizer\Token;
use Souplette\Html\Tokenizer\TokenType;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#the-before-html-insertion-mode
 */
final class BeforeHtml extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token::TYPE;
        if ($type === TokenType::DOCTYPE) {
            // TODO: Parse error. Ignore the token.
            return;
        } else if ($type === TokenType::COMMENT) {
            $tree->insertComment($token, new InsertionLocation($tree->document));
            return;
        } else if ($type === TokenType::CHARACTER) {
            $token->data = ltrim($token->data, " \n\t\f\r");
            if (\strlen($token->data) === 0) {
                // Ignore the token.
                return;
            }
            goto ANYTHING_ELSE;
        } else if ($type === TokenType::START_TAG && $token->name === 'html') {
            // Create an element for the token in the HTML namespace, with the Document as the intended parent.
            $element = $tree->createElement($token, Namespaces::HTML, $tree->document);
            // Append it to the Document object.
            $tree->document->appendChild($element);
            // Put this element in the stack of open elements.
            $tree->openElements->push($element);
            // Switch the insertion mode to "before head".
            $tree->insertionMode = InsertionModes::BEFORE_HEAD;
            return;
        } else if ($type === TokenType::END_TAG) {
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
