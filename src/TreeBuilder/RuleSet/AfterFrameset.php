<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder\RuleSet;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenTypes;
use ju1ius\HtmlParser\TreeBuilder\InsertionModes;
use ju1ius\HtmlParser\TreeBuilder\RuleSet;
use ju1ius\HtmlParser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-afterframeset
 */
final class AfterFrameset extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token->type;
        if ($type === TokenTypes::CHARACTER && ctype_space($token->data)) {
            // Insert the character.
            $tree->insertCharacter($token);
        } elseif ($type === TokenTypes::COMMENT) {
            // Insert a comment.
            $tree->insertComment($token);
        } elseif ($type === TokenTypes::DOCTYPE) {
            // TODO: Parse error. Ignore the token.
            return;
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'html') {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        } elseif ($type === TokenTypes::END_TAG && $token->name === 'html') {
            // Switch the insertion mode to "after after frameset".
            $tree->insertionMode = InsertionModes::AFTER_AFTER_FRAMESET;
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'noframes') {
            // Process the token using the rules for the "in head" insertion mode.
            InHead::process($token, $tree);
        } elseif ($type === TokenTypes::EOF) {
            // TODO: Stop parsing.
            return;
        } else {
            // TODO: Parse error. Ignore the token.
            return;
        }
    }
}
