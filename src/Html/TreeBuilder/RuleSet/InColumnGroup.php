<?php declare(strict_types=1);

namespace Souplette\Html\TreeBuilder\RuleSet;

use Souplette\Html\Tokenizer\Token;
use Souplette\Html\Tokenizer\TokenType;
use Souplette\Html\TreeBuilder;
use Souplette\Html\TreeBuilder\InsertionModes;
use Souplette\Html\TreeBuilder\RuleSet;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-incolgroup
 */
final class InColumnGroup extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token::TYPE;
        if ($type === TokenType::CHARACTER) {
            $l = strspn($token->data, " \n\t\f");
            if ($l === \strlen($token->data)) {
                // Insert the character.
                $tree->insertCharacter($token);
                return;
            }
            if ($l > 0) {
                // Insert the character.
                $tree->insertCharacter($token, substr($token->data, 0, $l));
                $token->data = substr($token->data, $l);
            }
            goto ANYTHING_ELSE;
        } else if ($type === TokenType::COMMENT) {
            // Insert a comment.
            $tree->insertComment($token);
        } else if ($type === TokenType::DOCTYPE) {
            // TODO: Parse error.
            // Ignore the token
            return;
        } else if ($type === TokenType::START_TAG && $token->name === 'html') {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        } else if ($type === TokenType::START_TAG && $token->name === 'col') {
            // Insert an HTML element for the token. Immediately pop the current node off the stack of open elements.
            $tree->insertElement($token);
            $tree->openElements->pop();
            // Acknowledge the token's self-closing flag, if it is set.
            $tree->acknowledgeSelfClosingFlag($token);
        } else if ($type === TokenType::END_TAG && $token->name === 'colgroup') {
            // If the current node is not a colgroup element, then this is a parse error; ignore the token.
            if (!$tree->openElements->currentNodeHasType('colgroup')) {
                // TODO: Parse error.
                return;
            }
            // Otherwise, pop the current node from the stack of open elements.
            $tree->openElements->pop();
            // Switch the insertion mode to "in table".
            $tree->insertionMode = InsertionModes::IN_TABLE;
        } else if ($type === TokenType::END_TAG && $token->name === 'col') {
            // TODO: Parse error.
            // Ignore the token
            return;
        } else if (($type === TokenType::START_TAG || $type === TokenType::END_TAG) && $token->name === 'template') {
            // Process the token using the rules for the "in head" insertion mode.
            InHead::process($token, $tree);
        } else if ($type === TokenType::EOF) {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        } else {
            ANYTHING_ELSE:
            // If the current node is not a colgroup element, then this is a parse error; ignore the token.
            if (!$tree->openElements->currentNodeHasType('colgroup')) {
                // TODO: Parse error.
                return;
            }
            // Otherwise, pop the current node from the stack of open elements.
            $tree->openElements->pop();
            // Switch the insertion mode to "in table".
            $tree->insertionMode = InsertionModes::IN_TABLE;
            // Reprocess the token.
            $tree->processToken($token);
        }
    }
}
