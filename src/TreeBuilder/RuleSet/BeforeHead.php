<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder\RuleSet;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenTypes;
use ju1ius\HtmlParser\TreeBuilder\InsertionModes;
use ju1ius\HtmlParser\TreeBuilder\RuleSet;
use ju1ius\HtmlParser\TreeBuilder\TreeBuilder;

final class BeforeHead extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token->type;
        if ($type === TokenTypes::CHARACTER && ctype_space($token->data)) {
            // Ignore the token.
            return;
        } elseif ($type === TokenTypes::COMMENT) {
            $tree->insertComment($token);
            return;
        } elseif ($type === TokenTypes::DOCTYPE) {
            // TODO: Parse error. Ignore the token.
            return;
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'html') {
            InBody::process($token, $tree);
            return;
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'head') {
            $head = $tree->insertElement($token);
            $tree->headElement = $head;
            $tree->insertionMode = InsertionModes::IN_HEAD;
            return;
        } elseif ($type === TokenTypes::END_TAG) {
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
        $tree->insertionMode = InsertionModes::IN_HEAD;
        // Reprocess the current token.
        $tree->processToken($token);
    }
}
