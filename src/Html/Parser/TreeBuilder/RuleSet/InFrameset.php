<?php declare(strict_types=1);

namespace JoliPotage\Html\Parser\TreeBuilder\RuleSet;

use JoliPotage\Html\Parser\Tokenizer\Token;
use JoliPotage\Html\Parser\Tokenizer\TokenTypes;
use JoliPotage\Html\Parser\TreeBuilder\InsertionModes;
use JoliPotage\Html\Parser\TreeBuilder\RuleSet;
use JoliPotage\Html\Parser\TreeBuilder\TreeBuilder;

/**
 * @see https://html.spec.whatwg.org/multipage/parsing.html#parsing-main-inframeset
 */
final class InFrameset extends RuleSet
{
    public static function process(Token $token, TreeBuilder $tree)
    {
        $type = $token->type;
        if ($type === TokenTypes::CHARACTER) {
            $data = preg_replace('/[^ \n\t\f]+/S', '', $token->data, -1, $count);
            if ($count > 0) {
                // TODO: Parse error. Ignore the character tokens.
                if (strlen($data) === 0) return;
                $token->data = $data;
            }
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
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'frameset') {
            // Insert an HTML element for the token.
            $tree->insertElement($token);
        } elseif ($type === TokenTypes::END_TAG && $token->name === 'frameset') {
            // If the current node is the root html element, then this is a parse error; ignore the token. (fragment case)
            if ($tree->openElements->top() === $tree->document->documentElement) {
                // TODO: Parse error.
                return;
            }
            // Otherwise, pop the current node from the stack of open elements.
            $tree->openElements->pop();
            // If the parser was not created as part of the HTML fragment parsing algorithm (fragment case),
            // and the current node is no longer a frameset element,
            // then switch the insertion mode to "after frameset".
            if (!$tree->isBuildingFragment && $tree->openElements->top()->localName !== 'frameset') {
                $tree->insertionMode = InsertionModes::AFTER_FRAMESET;
            }
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'frame') {
            // Insert an HTML element for the token. Immediately pop the current node off the stack of open elements.
            $tree->insertElement($token);
            $tree->openElements->pop();
            // Acknowledge the token's self-closing flag, if it is set.
            $tree->acknowledgeSelfClosingFlag($token);
        } elseif ($type === TokenTypes::START_TAG && $token->name === 'noframes') {
            // Process the token using the rules for the "in head" insertion mode.
            InHead::process($token, $tree);
        } elseif ($type === TokenTypes::EOF) {
            // If the current node is not the root html element, then this is a parse error.
            if ($tree->openElements->top() === $tree->document->documentElement) {
                // TODO: Parse error.
                return;
            }
            // TODO: Stop parsing.
        } else {
            // TODO: Parse error. Ignore the token.
            return;
        }
    }
}
