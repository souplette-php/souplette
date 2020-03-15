<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder\RuleSet;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenTypes;
use ju1ius\HtmlParser\TreeBuilder\InsertionModes;
use ju1ius\HtmlParser\TreeBuilder\RuleSet;
use ju1ius\HtmlParser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-incolgroup
 */
final class InColumnGroup extends RuleSet
{
    public function process(Token $token, TreeBuilder $tree)
    {
        $type = $token->type;
        if ($type === TokenTypes::CHARACTER && ctype_space($token->data)) {
            // Insert the character.
            $tree->insertCharacter($token);
        } elseif ($type === TokenTypes::COMMENT) {
            // Insert a comment.
            $tree->insertComment($token);
        } elseif ($type === TokenTypes::DOCTYPE) {
            // TODO: Parse error.
            // Ignore the token
            return;
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'html') {
            // Process the token using the rules for the "in body" insertion mode.
            $tree->processToken($token, InsertionModes::IN_BODY);
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'col') {
            // Insert an HTML element for the token. Immediately pop the current node off the stack of open elements.
            $tree->insertElement($token);
            $tree->openElements->pop();
            // Acknowledge the token's self-closing flag, if it is set.
            $token->selfClosingAcknowledged = true;
        } elseif ($type === TokenTypes::END_TAG && $token->name === 'colgroup') {
            // If the current node is not a colgroup element, then this is a parse error; ignore the token.
            if ($tree->openElements->top()->localName !== 'colgroup') {
                // TODO: Parse error.
                return;
            }
            // Otherwise, pop the current node from the stack of open elements.
            $tree->openElements->pop();
            // Switch the insertion mode to "in table".
            $tree->setInsertionMode(InsertionModes::IN_TABLE);
        } elseif ($type === TokenTypes::END_TAG && $token->name === 'col') {
            // TODO: Parse error.
            // Ignore the token
            return;
        } elseif (($type === TokenTypes::START_TAG || $type === TokenTypes::END_TAG) && $token->name === 'template') {
            // Process the token using the rules for the "in head" insertion mode.
            $tree->processToken($token, InsertionModes::IN_HEAD);
        } elseif ($type === TokenTypes::EOF) {
            // Process the token using the rules for the "in body" insertion mode.
            $tree->processToken($token, InsertionModes::IN_BODY);
        } else {
            // If the current node is not a colgroup element, then this is a parse error; ignore the token.
            if ($tree->openElements->top()->localName !== 'colgroup') {
                // TODO: Parse error.
                return;
            }
            // Otherwise, pop the current node from the stack of open elements.
            $tree->openElements->pop();
            // Switch the insertion mode to "in table".
            $tree->setInsertionMode(InsertionModes::IN_TABLE);
            // Reprocess the token.
            $tree->processToken($token);
        }
    }
}
