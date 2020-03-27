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
    private const IN_HEAD_START_TAG_TRIGGERS = [
        'basefont' => true,
        'bgsound' => true,
        'link' => true,
        'meta' => true,
        'noframes' => true,
        'style' => true,
    ];

    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token->type;
        if ($type === TokenTypes::DOCTYPE) {
            // TODO: Parse error. Ignore the token.
            return;
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'html') {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
            return;
        } elseif ($type === TokenTypes::END_TAG && $token->name === 'noscript') {
            // Pop the current node (which will be a noscript element) from the stack of open elements;
            // the new current node will be a head element.
            $tree->openElements->pop();
            // Switch the insertion mode to "in head".
            $tree->insertionMode = InsertionModes::IN_HEAD;
            return;
        } elseif (
            ($type === TokenTypes::CHARACTER && ctype_space($token->data))
            || $type === TokenTypes::COMMENT
            || ($type === TokenTypes::START_TAG && isset(self::IN_HEAD_START_TAG_TRIGGERS[$token->name]))
        ) {
            // Process the token using the rules for the "in head" insertion mode.
            InHead::process($token, $tree);
            return;
        } elseif ($type === TokenTypes::END_TAG && $token->name === 'br') {
            // Act as described in the "anything else" entry below.
            goto ANYTHING_ELSE;
        } elseif (
            ($type === TokenTypes::START_TAG && ($token->name === 'head' || $token->name === 'noscript'))
            || $type === TokenTypes::END_TAG
        ) {
            // TODO: Parse error. Ignore the token.
            return;
        }
        ANYTHING_ELSE:
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
