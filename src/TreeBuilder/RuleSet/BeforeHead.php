<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder\RuleSet;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\TreeBuilder\InsertionLocation;
use ju1ius\HtmlParser\TreeBuilder\InsertionModes;
use ju1ius\HtmlParser\TreeBuilder\RuleSet;
use ju1ius\HtmlParser\TreeBuilder\TreeBuilder;

final class BeforeHead extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        if ($token instanceof Token\Character && ctype_space($token->data)) {
            // Ignore the token.
            return;
        } elseif ($token instanceof Token\Comment) {
            $tree->insertComment($token, new InsertionLocation($tree->document));
            return;
        } elseif ($token instanceof Token\Doctype) {
            // TODO: Parse error. Ignore the token.
            return;
        } elseif ($token instanceof Token\StartTag && $token->name === 'html') {
            $tree->processToken($token, InsertionModes::IN_BODY);
            return;
        } elseif ($token instanceof Token\StartTag && $token->name === 'head') {
            $head = $tree->insertElement($token);
            $tree->headElement = $head;
            $tree->setInsertionMode(InsertionModes::IN_HEAD);
            return;
        } elseif ($token instanceof Token\EndTag) {
            if ($token->name === 'head' || $token->name === 'body' || $token->name === 'html' || $token->name === 'br') {
                // Act as described in the "anything else" entry below.
            } else {
                // TODO: Parse error. Ignore the token.
                return;
            }
        }
        // Insert an HTML element for a "head" start tag token with no attributes.
        $head = $tree->insertElement(new Token\StartTag('head'));
        // Set the head element pointer to the newly created head element.
        $tree->headElement = $head;
        // Switch the insertion mode to "in head".
        $tree->setInsertionMode(InsertionModes::IN_HEAD);
        // Reprocess the current token.
        $tree->processToken($token);
    }
}
