<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder\RuleSet;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenTypes;
use ju1ius\HtmlParser\TreeBuilder\InsertionModes;
use ju1ius\HtmlParser\TreeBuilder\RuleSet;
use ju1ius\HtmlParser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-inheadnoscript
 */
final class InHeadNoscript extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token->type;
        if ($type === TokenTypes::COMMENT) {
            // TODO: Parse error. Ignore the token.
            return;
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'html') {
            // Process the token using the rules for the "in body" insertion mode.
            $tree->processToken($token, InsertionModes::IN_BODY);
            return;
        } elseif ($type === TokenTypes::END_TAG && $token->name === 'noscript') {
            // Pop the current node (which will be a noscript element) from the stack of open elements;
            // the new current node will be a head element.
            $tree->openElements->pop();
            // Switch the insertion mode to "in head".
            $tree->insertionMode = InsertionModes::IN_HEAD;
            return;
        } elseif (
            $type === TokenTypes::CHARACTER && ctype_space($token->data)
            || $type === TokenTypes::COMMENT
            || $type === TokenTypes::START_TAG && (
                $token->name === 'basefont'
                || $token->name === 'bgsound'
                || $token->name === 'link'
                || $token->name === 'meta'
                || $token->name === 'noframes'
                || $token->name === 'style'
            )
        ) {
            // Process the token using the rules for the "in head" insertion mode.
            $tree->processToken($token, InsertionModes::IN_HEAD);
            return;
        } elseif ($type === TokenTypes::END_TAG && $token->name === 'br') {
            // Act as described in the "anything else" entry below.
        } elseif (
            $type === TokenTypes::START_TAG && ($token->name === 'br' || $token->name === 'noscript')
            || $type === TokenTypes::END_TAG
        ) {
            // TODO: Parse error. Ignore the token.
            return;
        }
        // TODO: Parse error.
        // Pop the current node (which will be a noscript element) from the stack of open elements;
        // the new current node will be a head element.
        $tree->openElements->pop();
        // Switch the insertion mode to "in head".
        $tree->insertionMode = InsertionModes::IN_HEAD;
        // Reprocess the token.
        $tree->processToken($token);
    }
}
