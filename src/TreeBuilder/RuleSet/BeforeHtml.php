<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder\RuleSet;

use ju1ius\HtmlParser\Namespaces;
use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenTypes;
use ju1ius\HtmlParser\TreeBuilder\InsertionLocation;
use ju1ius\HtmlParser\TreeBuilder\InsertionModes;
use ju1ius\HtmlParser\TreeBuilder\RuleSet;
use ju1ius\HtmlParser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#the-before-html-insertion-mode
 */
final class BeforeHtml extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token->type;
        if ($type === TokenTypes::DOCTYPE) {
            // TODO: Parse error. Ignore the token.
            return;
        } elseif ($type === TokenTypes::COMMENT) {
            $tree->insertComment($token, new InsertionLocation($tree->document));
            return;
        } elseif ($type === TokenTypes::CHARACTER) {
            if (ctype_space($token->data)) {
                // Ignore the token.
                return;
            } else {
                $token->data = ltrim($token->data, " \n\t\f");
                goto ANYTHING_ELSE;
            }
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'html') {
            // Create an element for the token in the HTML namespace, with the Document as the intended parent.
            $element = $tree->createElement($token, Namespaces::HTML, $tree->document);
            // Append it to the Document object.
            $tree->document->appendChild($element);
            // Put this element in the stack of open elements.
            $tree->openElements->push($element);
            // Switch the insertion mode to "before head".
            $tree->insertionMode = InsertionModes::BEFORE_HEAD;
            return;
        } elseif ($type === TokenTypes::END_TAG) {
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
