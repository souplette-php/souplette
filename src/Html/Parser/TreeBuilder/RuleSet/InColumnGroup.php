<?php declare(strict_types=1);

namespace Souplette\Html\Parser\TreeBuilder\RuleSet;

use Souplette\Html\Parser\Tokenizer\Token;
use Souplette\Html\Parser\Tokenizer\TokenTypes;
use Souplette\Html\Parser\TreeBuilder\InsertionModes;
use Souplette\Html\Parser\TreeBuilder\RuleSet;
use Souplette\Html\Parser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-incolgroup
 */
final class InColumnGroup extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token::TYPE;
        if ($type === TokenTypes::CHARACTER) {
            if (ctype_space($token->data)) {
                // Insert the character.
                $tree->insertCharacter($token);
                return;
            }
            if ($l = strspn($token->data, " \n\t\f")) {
                // Insert the character.
                $tree->insertCharacter($token, substr($token->data, 0, $l));
                $token->data = substr($token->data, $l);
            }
            goto ANYTHING_ELSE;
        } elseif ($type === TokenTypes::COMMENT) {
            // Insert a comment.
            $tree->insertComment($token);
        } elseif ($type === TokenTypes::DOCTYPE) {
            // TODO: Parse error.
            // Ignore the token
            return;
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'html') {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'col') {
            // Insert an HTML element for the token. Immediately pop the current node off the stack of open elements.
            $tree->insertElement($token);
            $tree->openElements->pop();
            // Acknowledge the token's self-closing flag, if it is set.
            $tree->acknowledgeSelfClosingFlag($token);
        } elseif ($type === TokenTypes::END_TAG && $token->name === 'colgroup') {
            // If the current node is not a colgroup element, then this is a parse error; ignore the token.
            if ($tree->openElements->top()->localName !== 'colgroup') {
                // TODO: Parse error.
                return;
            }
            // Otherwise, pop the current node from the stack of open elements.
            $tree->openElements->pop();
            // Switch the insertion mode to "in table".
            $tree->insertionMode = InsertionModes::IN_TABLE;
        } elseif ($type === TokenTypes::END_TAG && $token->name === 'col') {
            // TODO: Parse error.
            // Ignore the token
            return;
        } elseif (($type === TokenTypes::START_TAG || $type === TokenTypes::END_TAG) && $token->name === 'template') {
            // Process the token using the rules for the "in head" insertion mode.
            InHead::process($token, $tree);
        } elseif ($type === TokenTypes::EOF) {
            // Process the token using the rules for the "in body" insertion mode.
            InBody::process($token, $tree);
        } else {
            ANYTHING_ELSE:
            // If the current node is not a colgroup element, then this is a parse error; ignore the token.
            if ($tree->openElements->top()->localName !== 'colgroup') {
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
